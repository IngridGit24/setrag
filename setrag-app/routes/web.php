<?php

use App\Http\Controllers\Admin\TripController as AdminTripController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\SuccessController;
use App\Http\Controllers\TrackController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/book', [BookController::class, 'index'])->name('book');
Route::get('/track', [TrackController::class, 'index'])->name('track');
Route::get('/shipping', [ShippingController::class, 'index'])->name('shipping');

// Auth routes
Route::get('/auth', [AuthController::class, 'showLogin'])->name('auth');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Booking flow
Route::post('/payment/store', [PaymentController::class, 'store'])->name('payment.store');
Route::get('/payment', [PaymentController::class, 'show'])->name('payment');
Route::post('/payment', [PaymentController::class, 'process'])->name('payment.process');
Route::get('/payment/success', [PaymentController::class, 'handleSuccess'])->name('payment.success');
Route::get('/payment/failed', [PaymentController::class, 'handleFailure'])->name('payment.failed');
Route::get('/success', [SuccessController::class, 'index'])->name('success');

// Protected routes
Route::middleware('auth.session')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
});

// Admin routes
Route::prefix('admin')->middleware('auth.session')->name('admin.')->group(function () {
    Route::resource('trips', AdminTripController::class);
    Route::post('trips/{trip}/generate-seats', [AdminTripController::class, 'generateSeats'])->name('trips.generate-seats');
});
