<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarController;

Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::get('/login', [AuthController::class, 'showLogin']);
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 🔒 GRUP ADMIN (WAJIB LOGIN)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [CarController::class, 'dashboard'])->name('dashboard');
    Route::get('/infentory', [CarController::class, 'index'])->name('inventory');
    Route::get('/vehicle/detail/{id}', [CarController::class, 'show'])->name('Car.detail');
    Route::get('/vehicle/edit/{id}', [CarController::class, 'edit'])->name('Car.edit');
    Route::put('/vehicle/update/{id}', [CarController::class, 'update'])->name('Car.update');
    
    Route::get('inventory/Addnew', function(){
        return view('AddNewCar');
    })->name('AddNew');

    Route::post('/car/store', [CarController::class, 'store'])->name('Car.store');
    Route::delete('/vehicle/{id}', [CarController::class, 'destroy'])->name('Car.destroy');
    Route::patch('/car/{car}/update-status',[CarController::class,'updateStatus'])->name('Car.updateStatus');
}); // <--- PASTIKAN GRUP AUTH SUDAH DITUTUP DI SINI!

// 🌍 RUTE PUBLIC UNTUK POSTMAN (WAJIB DI LUAR GRUP)
Route::post('/api/submit-offer', [CarController::class, 'submitOffer']);