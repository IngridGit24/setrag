<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\PricingController;
use App\Http\Controllers\Api\SeatController;
use App\Http\Controllers\Api\StationController;
use App\Http\Controllers\Api\TrackingController;
use App\Http\Controllers\Api\TripController;
use Illuminate\Support\Facades\Route;

// Health check
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

// Auth routes
Route::prefix('oauth')->group(function () {
    Route::post('/token', [AuthController::class, 'token']);
});

Route::post('/users', [AuthController::class, 'register']);

// Protected routes
Route::middleware(['jwt.auth'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/booking', [BookingController::class, 'store']);
});

// Public API routes
Route::prefix('stations')->group(function () {
    Route::get('/', [StationController::class, 'index']);
    Route::get('/{id}', [StationController::class, 'show']);
    Route::post('/', [StationController::class, 'store']);
});

Route::prefix('trips')->group(function () {
    Route::get('/', [TripController::class, 'index']);
    Route::get('/{id}', [TripController::class, 'show']);
    Route::post('/', [TripController::class, 'store']);
    
    Route::prefix('{tripId}/seats')->group(function () {
        Route::get('/', [SeatController::class, 'listSeats']);
        Route::post('/seed', [SeatController::class, 'seedSeats']);
        Route::post('/allocate', [SeatController::class, 'allocateSeat']);
        Route::post('/{seatNo}/confirm', [SeatController::class, 'confirmSeat']);
    });
});

// Pricing routes
Route::post('/price/quote', [PricingController::class, 'quote']);

// EBILLING Callback (public endpoint for payment notifications)
Route::post('/ebilling/callback', [\App\Http\Controllers\Api\EbillingCallbackController::class, 'handleCallback']);

// Tracking routes
Route::prefix('positions')->group(function () {
    Route::get('/', [TrackingController::class, 'index']);
    Route::post('/', [TrackingController::class, 'store']);
    Route::get('/search', [TrackingController::class, 'search']);
});

Route::prefix('trains')->group(function () {
    Route::get('/{trainId}/position', [TrackingController::class, 'getTrainPosition']);
});

Route::post('/position', [TrackingController::class, 'storeSingle']);

