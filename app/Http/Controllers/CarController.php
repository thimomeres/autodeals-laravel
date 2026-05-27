<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\OfferCancelled;
use App\Events\OfferSubmitted;
use App\Events\VehicleStockUpdated;
use App\Http\Resources\CarCatalogResource;
use App\Http\Resources\CarDetailResource;
use App\Http\Resources\MobileOfferResource;
use App\Models\Car;
use App\Models\CarImage;
use App\Models\Customer;
use App\Models\Offer;
use App\Services\ActivityLogger;
use App\Support\BroadcastPayload;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse; 

class CarController extends Controller
{
    private const MAX_PRICE = 9999999999999999.99;

    private function normalizeVehicleInput(Request $request): void
    {
        $merge = [];

        foreach (['price', 'mileage', 'year', 'engine_capacity_cc', 'fuel_tank_capacity', 'seating_capacity'] as $field) {
            if (! $request->has($field)) {
                continue;
            }

            $raw = (string) $request->input($field);
            $digits = preg_replace('/[^\d]/', '', $raw);

            if ($digits === '') {
                continue;
            }

            $merge[$field] = $field === 'price'
                ? (float) $digits
                : (int) $digits;
        }

        if ($merge !== []) {
            $request->merge($merge);
        }
    }

    private function vehicleValidationRules(bool $isCreate = false): array
    {
        $rules = [
            'brand'              => 'required|string|max:100',
            'model'              => 'required|string|max:100',
            'year'               => 'required|integer|min:1980|max:' . (date('Y') + 1),
            'price'              => 'required|numeric|min:1|max:' . self::MAX_PRICE,
            'mileage'            => 'required|integer|min:0|max:9999999',
            'color'              => 'required|string|max:50',
            'transmission'       => 'required|string|max:50',
            'fuel_type'          => 'required|string|max:50',
            'engine_capacity_cc' => 'required|integer|min:1|max:20000',
            'condition'          => 'required|string|max:100',
            'fuel_tank_capacity' => 'required|integer|min:1|max:500',
            'seating_capacity'   => 'required|integer|min:1|max:99',
            'plate_number'       => 'nullable|string|max:50',
            'vin_number'         => 'nullable|string|max:100',
            'description'        => 'nullable|string|max:5000',
            'images'             => ($isCreate ? 'required' : 'nullable') . '|array|max:10',
            'images.*'           => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        ];

        if ($isCreate) {
            $rules['stock_code'] = 'required|unique:cars,stock_code|max:50';
        }

        return $rules;
    }

    public function index()
    {
        // 🟢 Mengambil data mobil beserta relasi gambarnya agar lebih ringan (Eager Loading)
        $cars = Car::with('images')->get();
        
        // Menghitung jumlah notifikasi riil untuk halaman Inventory
        $unreadNotificationsCount = Offer::where('status', 'pending_review')->count();

        // 🛠️ Mengembalikan view 'infentory' sesuai nama file fisik Anda (infentory.blade.php)
        return view('infentory', compact('cars', 'unreadNotificationsCount'));
    }

    // =========================================================================
    // 📱 API PENAWARAN (Flutter / Postman) → dashboard admin real-time
    // =========================================================================

    /**
     * POST /api/offers — terima penawaran dari aplikasi mobile.
     * Body: buyer_name, car_id (atau target_vehicle), price_offered
     */
    public function storeOffer(Request $request): JsonResponse
    {
        if (! $request->filled('car_id') && $request->filled('target_vehicle')) {
            $request->merge(['car_id' => $request->input('target_vehicle')]);
        }

        $validated = $request->validate([
            'car_id'        => 'required|integer|exists:cars,id',
            'buyer_name'    => ['required', 'string', 'max:255', 'regex:/^[\pL\pM\pN\s.\'\-]+$/u'],
            'price_offered' => 'required|numeric|min:1|max:' . self::MAX_PRICE,
        ]);

        $car = Car::find($validated['car_id']);

        if (! $car || strtolower((string) $car->status) === 'sold') {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle is not available for new offers.',
            ], 422);
        }

        $authCustomer = $request->user();
        $buyerName = trim($validated['buyer_name']);
        $customerId = null;

        if ($authCustomer instanceof Customer) {
            $buyerName = $authCustomer->name;
            $customerId = $authCustomer->id;
        } else {
            $customerId = Customer::where('name', $buyerName)->value('id');
        }

        $offer = Offer::create([
            'car_id'        => $validated['car_id'],
            'customer_id'   => $customerId,
            'buyer_name'    => $buyerName,
            'price_offered' => $validated['price_offered'],
            'status'        => 'pending_review',
        ]);

        if ($car->status !== 'sold') {
            $car->update(['status' => 'pending']);
        }

        $offer->load('car');

        event(new OfferSubmitted($offer));
        $this->broadcastVehicleStock($car, 'offer_submitted');

        ActivityLogger::log(
            'offer.submitted',
            "Penawaran masuk dari {$offer->buyer_name} untuk {$car->brand} {$car->model}",
            Offer::class,
            $offer->id,
            ['car_id' => $car->id, 'price_offered' => $offer->price_offered],
        );

        return response()->json([
            'success' => true,
            'message' => 'Offer submitted successfully. Pending admin review.',
            'data' => [
                'id' => $offer->id,
                'car_id' => $offer->car_id,
                'buyer_name' => $offer->buyer_name,
                'price_offered' => $offer->price_offered,
                'status' => $offer->status,
                'status_label' => 'Pending Review',
                'vehicle' => [
                    'brand' => $car->brand,
                    'model' => $car->model,
                    'stock_code' => $car->stock_code,
                ],
            ],
        ], 201);
    }

    /** @deprecated Gunakan POST /api/offers — tetap didukung untuk Postman lama */
    public function submitOffer(Request $request): JsonResponse
    {
        return $this->storeOffer($request);
    }

    /**
     * GET /api/my-offers?buyer_name=Timo
     */
    public function myOffers(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'buyer_name' => ['required', 'string', 'max:255'],
        ]);

        $offers = Offer::with('car')
            ->where('buyer_name', trim($validated['buyer_name']))
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Offer history retrieved successfully.',
            'count' => $offers->count(),
            'data' => MobileOfferResource::collection($offers),
        ], 200);
    }

    /**
     * POST|PUT /api/offers/{id}/cancel
     */
    public function cancelOffer(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'buyer_name' => ['required', 'string', 'max:255'],
        ]);

        $offer = Offer::with('car')->find($id);

        if (! $offer) {
            return response()->json([
                'success' => false,
                'message' => 'Offer not found.',
            ], 404);
        }

        if (strcasecmp(trim($offer->buyer_name), trim($validated['buyer_name'])) !== 0) {
            return response()->json([
                'success' => false,
                'message' => 'You are not allowed to cancel this offer.',
            ], 403);
        }

        if ($offer->status !== 'pending_review') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending offers can be cancelled.',
                'current_status' => $offer->status,
            ], 422);
        }

        $offer->update(['status' => 'cancelled']);

        $this->restoreCarAvailabilityIfNoPendingOffers($offer->car_id);

        $offer->refresh()->load('car');
        $car = $offer->car;
        $vehicleLabel = trim(($car->brand ?? 'Mobil') . ' ' . ($car->model ?? ''));

        event(new OfferCancelled($offer, $vehicleLabel));

        if ($car) {
            $this->broadcastVehicleStock($car, 'offer_cancelled');
        }

        ActivityLogger::log(
            'offer.cancelled',
            "Penawaran dibatalkan oleh {$offer->buyer_name} untuk {$vehicleLabel}",
            Offer::class,
            $offer->id,
        );

        return response()->json([
            'success' => true,
            'message' => 'Offer cancelled successfully.',
            'data' => new MobileOfferResource($offer),
        ], 200);
    }

    private function broadcastVehicleStock(Car $car, string $reason): void
    {
        event(new VehicleStockUpdated($car->fresh(), $reason));
    }

    private function restoreCarAvailabilityIfNoPendingOffers(int $carId): void
    {
        $car = Car::find($carId);

        if (! $car || strtolower((string) $car->status) === 'sold') {
            return;
        }

        $pendingCount = Offer::where('car_id', $carId)
            ->where('status', 'pending_review')
            ->count();

        if ($pendingCount === 0 && strtolower((string) $car->status) === 'pending') {
            $car->update(['status' => 'available']);
        }
    }

    // --- MENYESUAIKAN INPUT BARU DARI FORM ---
    public function store(Request $request)
    {
        $this->normalizeVehicleInput($request);

        $validated = $request->validate($this->vehicleValidationRules(isCreate: true));

        $validated['created_by'] = Auth::id(); 
        $validated['status'] = 'available'; 

        $car = Car::create($validated);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('cars', 'public');
                CarImage::create([
                    'car_id'     => $car->id, 
                    'image_path' => $path     
                ]);
            }
        }

        ActivityLogger::log(
            'car.created',
            "Unit baru ditambahkan: {$car->brand} {$car->model} ({$car->stock_code})",
            Car::class,
            $car->id,
        );

        return redirect()->route('inventory')->with('success', 'New vehicle and photos added successfully!');
    }

    public function show($id)
    {
        $car = Car::with(['images', 'offers' => fn ($q) => $q->latest()])->findOrFail($id);

        return view('CardDetail', compact('car'));
    }

    public function edit($id)
    {
        $car = Car::with('images')->findOrFail($id);
        return view('AddNewCar', compact('car'));
    }

    public function update(Request $request, $id)
    {
        $car = Car::findOrFail($id);

        $this->normalizeVehicleInput($request);

        $validated = $request->validate($this->vehicleValidationRules(isCreate: false));

        $car->update([
            'condition'           => $validated['condition'],
            'brand'               => $validated['brand'],
            'model'               => $validated['model'],
            'year'                => $validated['year'],
            'price'               => $validated['price'],
            'mileage'             => $validated['mileage'],
            'color'               => $validated['color'],
            'transmission'        => $validated['transmission'],
            'fuel_type'           => $validated['fuel_type'],
            'engine_capacity_cc'  => $validated['engine_capacity_cc'],
            'plate_number'        => $validated['plate_number'] ?? null,
            'fuel_tank_capacity'  => $validated['fuel_tank_capacity'],
            'seating_capacity'    => $validated['seating_capacity'],
            'vin_number'          => $validated['vin_number'] ?? null,
            'description'         => $validated['description'] ?? null,
        ]);

        if ($request->hasFile('images')) {
            foreach ($car->images as $oldImage) {
                Storage::disk('public')->delete($oldImage->image_path);
                $oldImage->delete();
            }

            foreach ($request->file('images') as $file) {
                $path = $file->store('cars', 'public');
                $car->images()->create(['image_path' => $path]);
            }
        }

        ActivityLogger::log(
            'car.updated',
            "Data unit diperbarui: {$car->brand} {$car->model} ({$car->stock_code})",
            Car::class,
            $car->id,
        );

        return redirect()->route('inventory')->with('success', 'Vehicle data updated successfully!');
    }

    public function destroy($id)
    {
        $car = Car::with('images')->findOrFail($id);
        $label = "{$car->brand} {$car->model} ({$car->stock_code})";

        foreach ($car->images as $image) {
            if (Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }
        }

        $car->delete();

        ActivityLogger::log('car.deleted', "Unit dihapus: {$label}");

        return redirect()->route('inventory')->with('success', 'Vehicle and its associated assets have been permanently deleted.');
    }

    public function dashboard()
    {
        $totalRaw = Car::where('status', 'available')->sum('price');
        $totalInventoryValue = number_format($totalRaw / 1000000000, 1, '.', '');

        $unitsSold = Offer::where('status', 'accepted')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();

        $salesTarget = config('autodeals.monthly_sales_target', 10);
        $targetPercentage = $salesTarget > 0
            ? min(100, (int) round(($unitsSold / $salesTarget) * 100))
            : 0;

        // 3. Menghitung Sebaran Stok Riil dari Database berdasarkan Brand
        $toyotaCount = Car::where('brand', 'Toyota')->count();
        $hondaCount = Car::where('brand', 'Honda')->count();
        
        $bmwMercedesCount = Car::whereIn('brand', ['BMW', 'Mercedes'])->count();
        $totalCars = Car::count();

        $toyotaPercent = $totalCars > 0 ? round(($toyotaCount / $totalCars) * 100) : 0;
        $hondaPercent = $totalCars > 0 ? round(($hondaCount / $totalCars) * 100) : 0;
        $bmwPercent = $totalCars > 0 ? round(($bmwMercedesCount / $totalCars) * 100) : 0;

        // --- MENGHUBUNGKAN DATA NOTIFIKASI & TABEL BAWAH SECARA RIIL ---
        $unreadNotificationsCount = Offer::where('status', 'pending_review')->count();
        $recentOffers = Offer::with('car')->where('status', 'pending_review')->latest()->get();

        return view('dashboard', compact(
            'totalInventoryValue', 
            'unitsSold', 
            'salesTarget', 
            'targetPercentage',
            'toyotaCount', 'toyotaPercent',
            'hondaCount', 'hondaPercent',
            'bmwMercedesCount', 'bmwPercent',
            'unreadNotificationsCount',
            'recentOffers'
        ));
    }

    /**
     * GET /dashboard/pending-offers-sync — fallback polling jika WebSocket putus.
     */
    public function dashboardPendingOffersSync(): JsonResponse
    {
        $offers = Offer::with('car')
            ->where('status', 'pending_review')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'pending_review_count' => $offers->count(),
            'offers' => $offers->map(
                fn (Offer $offer) => BroadcastPayload::offerSubmittedEnvelope($offer)
            )->values(),
        ]);
    }

    // =========================================================================
    // ⚡ FITUR MAKSIMALISASI: UPDATE MANUAL DROPDOWN + SINKRONISASI DATA OFFER
    // =========================================================================
    public function updateStatus(Request $request, Car $car)
    {
        $request->validate([
            'status' => 'required|in:available,pending,sold',
        ]);

        $car->update([
            'status' => $request->status
        ]);

        // ✨ Tambahan Otomatisasi: Jika Admin manual mengubah status mobil, status tawaran ikut menyesuaikan
        if ($request->status === 'sold') {
            Offer::where('car_id', $car->id)->where('status', 'pending_review')->update(['status' => 'accepted']);
        } elseif ($request->status === 'available') {
            Offer::where('car_id', $car->id)->where('status', 'pending_review')->update(['status' => 'rejected']);
        }

        ActivityLogger::log(
            'car.status_updated',
            "Status {$car->stock_code} diubah menjadi {$request->status}",
            Car::class,
            $car->id,
        );

        $this->broadcastVehicleStock($car, 'admin_status_updated');

        return redirect()->back()->with('success', 'Status mobil berhasil diperbarui menjadi ' . ucfirst($request->status));
    }

    public function sales()
    {
        $salesData = Offer::with('car')
            ->where('status', 'accepted')
            ->latest()
            ->get();

        $totalRevenue = 0;
        $totalProfit = 0;

        foreach ($salesData as $sale) {
            $dealPrice = $sale->price_offered ?? 0;
            $baseCost = $sale->car->price ?? 0;

            $totalRevenue += $dealPrice;
            $totalProfit += $dealPrice - $baseCost;
        }

        $unreadNotificationsCount = Offer::where('status', 'pending_review')->count();

        return view('sales', compact(
            'salesData',
            'totalRevenue',
            'totalProfit',
            'unreadNotificationsCount'
        ));
    }

    public function exportSalesCsv(): StreamedResponse
    {
        $salesData = Offer::with('car')
            ->where('status', 'accepted')
            ->latest()
            ->get();

        $filename = 'autodeals-sales-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($salesData) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Invoice Date',
                'Buyer Name',
                'Brand',
                'Model',
                'Stock Code',
                'Base Cost',
                'Deal Price',
                'Net Profit',
            ]);

            foreach ($salesData as $sale) {
                $baseCost = $sale->car->price ?? 0;
                $dealPrice = $sale->price_offered ?? 0;
                fputcsv($handle, [
                    $sale->updated_at->format('Y-m-d H:i'),
                    $sale->buyer_name,
                    $sale->car->brand ?? '',
                    $sale->car->model ?? '',
                    $sale->car->stock_code ?? '',
                    $baseCost,
                    $dealPrice,
                    $dealPrice - $baseCost,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    // =========================================================================
    // 📱 MOBILE API (Flutter) — katalog publik, tanpa session admin
    // =========================================================================

    public function getAvailableCars(): JsonResponse
    {
        $cars = Car::with('images')
            ->where('status', 'available')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Available vehicles retrieved successfully.',
            'count' => $cars->count(),
            'data' => CarCatalogResource::collection($cars),
        ], 200);
    }

    public function getCarDetail(int $id): JsonResponse
    {
        $car = Car::with('images')->find($id);

        if (! $car) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Vehicle detail retrieved successfully.',
            'data' => new CarDetailResource($car),
        ], 200);
    }
}