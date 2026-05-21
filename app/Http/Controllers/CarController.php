<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Car;
use App\Models\CarImage; 
use App\Models\Offer; // <--- WAJIB IMPORT MODEL OFFER DI SINI
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; 

class CarController extends Controller
{
    public function index()
    {
        $cars = Car::all();
        
        // Menghitung jumlah notifikasi riil untuk halaman Inventory
        $unreadNotificationsCount = Offer::where('status', 'pending_review')->count();

        return view('infentory', compact('cars', 'unreadNotificationsCount'));
    }

    // --- FITUR BARU: MENERIMA DATA DARI POSTMAN ---
    public function submitOffer(Request $request)
    {
        // Validasi data kiriman dari Postman
        $validated = $request->validate([
            'car_id'        => 'required|exists:cars,id',
            'buyer_name'    => 'required|string|max:255',
            'price_offered' => 'required|numeric',
        ]);

        // Simpan data penawaran baru ke database MySQL
        Offer::create($validated);

        // Kembalikan respon sukses berformat JSON ke Postman
        return response()->json([
            'success' => true,
            'message' => 'Offer submitted successfully to AutoDeals system!'
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

        return redirect()->route('inventory')->with('success', 'New vehicle and photos added successfully!');
    }

    public function show($id)
    {
        $car = Car::with('images')->findOrFail($id);
        return view('CardDetail', compact('car'));
    }

    public function edit($id)
    {
        $car = Car::with('images')->findOrFail($id);
        return view('AddNewCar', compact('car'));
    }

    // --- SAAT UPDATE, FOTO DISET 'NULLABLE' (OPSIONAL) ---
    public function update(Request $request, $id)
    {
        $car = Car::findOrFail($id);

        $request->validate([
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

        return redirect()->to('/infentory')->with('success', 'Vehicle data updated successfully!');
    }

    public function destroy($id)
    {
        $car = Car::with('images')->findOrFail($id);

        foreach ($car->images as $image) {
            if (Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }
        }

        $car->delete();

        return redirect()->route('inventory')->with('success', 'Vehicle and its associated assets have been permanently deleted.');
    }

    public function dashboard()
    {
        // 1. Menghitung Total Nilai Investasi (Mobil berstatus 'available') dalam satuan Milyar (B)
        $totalRaw = Car::where('status', 'available')->sum('price');
        $totalInventoryValue = number_format($totalRaw / 1000000000, 1, '.', '');

        // 2. Simulasi Target Penjualan (Statis sesuai template Anda)
        $unitsSold = 45; 
        $salesTarget = 60;
        $targetPercentage = 75;

        // 3. Menghitung Sebaran Stok Riil dari Database berdasarkan Brand
        $toyotaCount = Car::where('brand', 'Toyota')->count();
        $hondaCount = Car::where('brand', 'Honda')->count();
        $bmwMercedesCount = Car::where('brand', 'BMW')->orWhere('brand', 'Mercedes')->count();
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

    public function updateStatus(Request $request, Car $car)
    {
        $request->validate([
            'status' => 'required|in:available,pending,sold',
        ]);

        $car->update([
            'status' => $request->status
        ]);

        return redirect()->back()->with('success', 'Status mobil berhasil diperbarui menjadi ' . ucfirst($request->status));
    }
}