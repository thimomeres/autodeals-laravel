<?php

use App\Http\Controllers\CarController;
use App\Http\Controllers\CustomerNotificationController;
use App\Http\Controllers\MobileAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile App API (Flutter) — stateless, no session / CSRF
|--------------------------------------------------------------------------
*/
Route::get('/cars', [CarController::class, 'getAvailableCars']);
Route::get('/cars/{id}', [CarController::class, 'getCarDetail'])->whereNumber('id');

Route::post('/register', [MobileAuthController::class, 'register'])
    ->middleware('throttle:10,1');

Route::post('/login', [MobileAuthController::class, 'login'])
    ->middleware('throttle:10,1');

Route::post('/logout', [MobileAuthController::class, 'logout'])
    ->middleware(['auth:sanctum', 'throttle:30,1']);

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::get('/notifications', [CustomerNotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [CustomerNotificationController::class, 'markAsRead'])->whereNumber('id');
});

Route::middleware(['offer.api', 'throttle:60,1'])->group(function () {
    Route::post('/offers', [CarController::class, 'storeOffer']);

    Route::get('/my-offers', [CarController::class, 'myOffers']);

    Route::post('/offers/{id}/cancel', [CarController::class, 'cancelOffer'])->whereNumber('id');
    Route::put('/offers/{id}/cancel', [CarController::class, 'cancelOffer'])->whereNumber('id');
});
