<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\OfferSubmitted;
use App\Models\Car;
use App\Models\CarImage; 
use App\Models\Offer;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse; 

class CarController extends Controller
{
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
    // ⚡ FITUR UTAMA: MENERIMA DATA DARI POSTMAN + OTOMATISASI STATUS MOBIL
    // =========================================================================
    public function submitOffer(Request $request)
    {
        $validated = $request->validate([
            'car_id'        => 'required|integer|exists:cars,id',
            'buyer_name'    => ['required', 'string', 'max:255', 'regex:/^[\pL\pM\pN\s.\'\-]+$/u'],
            'price_offered' => 'required|numeric|min:1|max:999999999999999',
        ]);

        $car = Car::find($validated['car_id']);

        if (! $car || strtolower((string) $car->status) === 'sold') {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle is not available for new offers.',
            ], 422);
        }

        $offer = Offer::create([
            'car_id'        => $validated['car_id'],
            'buyer_name'    => trim($validated['buyer_name']),
            'price_offered' => $validated['price_offered'],
            'status'        => 'pending_review',
        ]);

        $offer->load('car');

        event(new OfferSubmitted($offer));

        if ($car->status !== 'sold') {
            $car->update(['status' => 'pending']);
        }

        ActivityLogger::log(
            'offer.submitted',
            "Penawaran masuk dari {$offer->buyer_name} untuk {$car->brand} {$car->model}",
            Offer::class,
            $offer->id,
            ['car_id' => $car->id, 'price_offered' => $offer->price_offered],
        );

        return response()->json([
            'success' => true,
            'message' => 'Offer submitted successfully. Vehicle status updated to pending.',
            'data' => [
                'id' => $offer->id,
                'car_id' => $offer->car_id,
                'buyer_name' => $offer->buyer_name,
                'price_offered' => $offer->price_offered,
                'status' => $offer->status,
            ],
        ], 201);
    }

    // --- MENYESUAIKAN INPUT BARU DARI FORM ---
    public function store(Request $request)
    {
        $validated = $request->validate([
            'stock_code'         => 'required|unique:cars,stock_code',
            'brand'              => 'required|string',
            'model'              => 'required|string',
            'year'               => 'required|integer',
            'price'              => 'required|numeric',
            'mileage'            => 'required|integer',
            'color'              => 'required|string',
            'transmission'       => 'required|string',
            'fuel_type'          => 'required|string',
            'engine_capacity_cc' => 'required|integer',
            'condition'          => 'required|string',
            'fuel_tank_capacity' => 'required|integer', 
            'seating_capacity'   => 'required|integer', 
            'plate_number'       => 'nullable|string',  
            'vin_number'         => 'nullable|string',  
            'description'        => 'nullable|string',
            'images'             => 'required|array|max:10',
            'images.*'           => 'image|mimes:jpeg,png,jpg,webp|max:2048' 
        ]);

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

        $request->validate([
            'brand'              => 'required|string',
            'model'              => 'required|string',
            'year'               => 'required|integer',
            'price'              => 'required|numeric',
            'mileage'            => 'required|integer',
            'color'               => 'required|string',
            'transmission'        => 'required|string',
            'fuel_type'           => 'required|string',
            'engine_capacity_cc'  => 'required|integer',
            'condition'          => 'required|string',
            'fuel_tank_capacity' => 'required|integer',
            'seating_capacity'   => 'required|integer',
            'plate_number'       => 'nullable|string',
            'vin_number'         => 'nullable|string',
            'description'         => 'nullable|string',
            'images'             => 'nullable|array|max:10',
            'images.*'           => 'image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        $car->update([
            'condition'           => $request->condition,
            'brand'               => $request->brand,
            'model'               => $request->model,
            'year'                => $request->year,
            'price'               => $request->price,
            'mileage'             => $request->mileage,
            'color'               => $request->color,
            'transmission'        => $request->transmission,
            'fuel_type'           => $request->fuel_type,
            'engine_capacity_cc'  => $request->engine_capacity_cc,
            'plate_number'        => $request->plate_number,
            'fuel_tank_capacity'  => $request->fuel_tank_capacity,
            'seating_capacity'    => $request->seating_capacity,
            'vin_number'          => $request->vin_number,
            'description'         => $request->description,
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

        return redirect()->back()->with('success', 'Status mobil berhasil diperbarui menjadi ' . ucfirst($request->status));
    }

    // =========================================================================
    // ⚡ PANEL TINJAUAN: ADMIN MENYETUJUI PENAWARAN (ACCEPT)
    // =========================================================================
    public function acceptOffer($id)
    {
        $offer = Offer::findOrFail($id);

        if ($offer->status !== 'pending_review') {
            return redirect()->back()->with('error', 'Only pending offers can be accepted.');
        }

        $offer->update(['status' => 'accepted']);

        // 2. ✨ AUTO-UPDATE: Ubah status ketersediaan unit mobil menjadi 'sold'
        $car = Car::find($offer->car_id);
        if ($car) {
            $car->update(['status' => 'sold']);
        }

        // 3. ✨ AUTO-CLEANUP: Tolak semua penawaran pending lainnya khusus untuk mobil ini
        Offer::where('car_id', $offer->car_id)
             ->where('id', '!=', $offer->id)
             ->where('status', 'pending_review')
             ->update(['status' => 'rejected']);

        ActivityLogger::log(
            'offer.accepted',
            "Penawaran diterima dari {$offer->buyer_name}",
            Offer::class,
            $offer->id,
        );

        return redirect()->back()->with('success', 'Offer accepted! The vehicle is now marked as SOLD.');
    }

    // =========================================================================
    // ⚡ PANEL TINJAUAN: ADMIN MENOLAK PENAWARAN (REJECT)
    // =========================================================================
    public function rejectOffer($id)
    {
        $offer = Offer::findOrFail($id);

        if ($offer->status !== 'pending_review') {
            return redirect()->back()->with('error', 'Only pending offers can be rejected.');
        }

        $offer->update(['status' => 'rejected']);

        // 2. ✨ AUTO-RESTORE: Periksa apakah masih ada sisa tawaran pending lain pada mobil ini?
        $remainingOffers = Offer::where('car_id', $offer->car_id)
                                ->where('id', '!=', $offer->id)
                                ->where('status', 'pending_review')
                                ->count();

        // Jika sudah tidak ada tawaran pending lainnya, kembalikan status unit mobil ke 'available'
        if ($remainingOffers === 0) {
            $car = Car::find($offer->car_id);
            if ($car) {
                $car->update(['status' => 'available']);
            }
        }

        ActivityLogger::log(
            'offer.rejected',
            "Penawaran ditolak dari {$offer->buyer_name}",
            Offer::class,
            $offer->id,
        );

        return redirect()->back()->with('success', 'Offer has been rejected successfully.');
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
}