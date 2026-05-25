<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;

// -------------------------------------------------------------------------
// 🔑 RUTE LOGIN (HANYA UNTUK TAMU / BELUM LOGIN)
// -------------------------------------------------------------------------
Route::middleware(['guest'])->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/login', [AuthController::class, 'showLogin']);
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// -------------------------------------------------------------------------
// 🔒 GRUP ADMIN (WAJIB LOGIN)
// -------------------------------------------------------------------------
Route::middleware(['auth'])->group(function () {
    // Dashboard & Inventory Utama
    Route::get('/dashboard', [CarController::class, 'dashboard'])->name('dashboard');
    Route::get('/infentory', [CarController::class, 'index'])->name('inventory');
    Route::get('/sales', [CarController::class, 'sales'])->name('sales');
    Route::get('/sales/export', [CarController::class, 'exportSalesCsv'])->name('sales.export');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::middleware('owner')->group(function () {
        Route::get('/admin/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/admin/users', [UserController::class, 'store'])->name('users.store');
        Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::get('/admin/activity', [ActivityLogController::class, 'index'])->name('activity.index');
    });

    // Manajemen Aset Kendaraan (CRUD & Status)
    Route::get('/vehicle/detail/{id}', [CarController::class, 'show'])->name('Car.detail');
    Route::get('/vehicle/edit/{id}', [CarController::class, 'edit'])->name('Car.edit');
    Route::put('/vehicle/update/{id}', [CarController::class, 'update'])->name('Car.update');
    Route::post('/car/store', [CarController::class, 'store'])->name('Car.store');
    Route::delete('/vehicle/{id}', [CarController::class, 'destroy'])
        ->middleware('owner')
        ->name('Car.destroy');
    Route::patch('/car/{car}/update-status', [CarController::class, 'updateStatus'])->name('Car.updateStatus');
    
    // Navigasi Form Tambah Kendaraan Baru
    Route::get('inventory/Addnew', function(){
        return view('AddNewCar');
    })->name('AddNew');

    // ✨ BARU: Rute Keputusan Penawaran Masuk (Accept & Reject)
    Route::patch('/offers/{offer}/accept', [CarController::class, 'acceptOffer'])->name('offers.accept');
    Route::patch('/offers/{offer}/reject', [CarController::class, 'rejectOffer'])->name('offers.reject');
});

Route::redirect('/inventory', '/infentory');

// -------------------------------------------------------------------------
// 🌍 RUTE PUBLIC UNTUK POSTMAN (WAJIB DI LUAR GRUP MIDDLEWARE AUTH)
// -------------------------------------------------------------------------
Route::post('/api/submit-offer', [CarController::class, 'submitOffer'])
    ->middleware(['offer.api', 'throttle:30,1']);