<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Auth\VerificationController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// Authenticated + Verified + Customer-only routes
Route::middleware(['auth:sanctum', 'verified', 'customer'])->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Cart Routes (only for customers)
    Route::prefix('cart')->group(function () {
        Route::post('/', [CartController::class, 'add']);
        Route::get('/', [CartController::class, 'index']);
        Route::put('/{rowId}', [CartController::class, 'update']);
        Route::delete('/{rowId}', [CartController::class, 'remove']);
        Route::post('/clear', [CartController::class, 'clear']);
    });

    // Order Routes (only for customers)
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);

    // Payment Route (only for customers)
    Route::post('/orders/{id}/pay', [OrderController::class, 'pay']);
});

// Admin-only routes
Route::middleware(['auth:sanctum', 'verified', 'admin'])->group(function () {
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
});

// Email verification routes
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->middleware(['signed'])
    ->name('verification.verify');

Route::post('/email/resend', [VerificationController::class, 'resend'])
    ->middleware('auth:sanctum')
    ->name('verification.resend');

// Google authentication
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

// Optional: Fallback for undefined routes
Route::fallback(function () {
    return response()->json(['message' => 'API route not found.'], 404);
});
