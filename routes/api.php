<?php

use App\Http\Controllers\Api\AdminCustomerController;
use App\Http\Controllers\Api\AdminOrderController;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LayananController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PembayaranController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OtpController;
use App\Http\Controllers\Api\ProfileController;

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return response()->json($request->user());
});

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);


Route::post('/send-otp', [OtpController::class, 'sendOtp']);
Route::post('/verify-otp', [OtpController::class, 'verifyOtp']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Layanan
Route::get('/layanan',[LayananController::class, 'index']);
Route::get('/layanan/{id}',[LayananController::class, 'show']);


Route::middleware(['auth:sanctum', 'admin'])->group(function () {
// Pembayaran
    Route::get('/admin/orders/{orderId}/pembayaran', [PembayaranController::class, 'adminShow']);
    Route::patch('/admin/orders/{orderId}/pembayaran/status', [PembayaranController::class, 'updateStatus']);

// Admin Orders
    Route::get('/admin/orders', [AdminOrderController::class, 'index']);
    Route::get('/admin/orders/{id}', [AdminOrderController::class, 'show']);
    Route::patch('/admin/orders/{id}/status', [AdminOrderController::class, 'updateStatus']);

// Admin Customers
    Route::get('/admin/customers', [AdminCustomerController::class, 'index']);
    Route::get('/admin/customers/{id}', [AdminCustomerController::class, 'show']);
});

// Customer
Route::middleware('auth:sanctum')->group(function () {
// Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);

// profile
    Route::patch('/profile', [ProfileController::class, 'update']);

// Pembayaran
    Route::post('/orders/{orderId}/pembayaran', [PembayaranController::class, 'store']);
    Route::get('/orders/{orderId}/pembayaran', [PembayaranController::class, 'show']);

// Layanan
    Route::post('/layanan', [LayananController::class, 'store']);
    Route::put('/layanan/{id}', [LayananController::class, 'update']);
    Route::delete('/layanan/{id}', [LayananController::class, 'destroy']);
    
});
