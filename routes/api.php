<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Api\OrderController;

use Illuminate\Support\Facades\Mail;



Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('products', ProductController::class);

    Route::prefix('cart')->group(function () {
        Route::post('/', [CartController::class, 'add']);
        Route::get('/', [CartController::class, 'index']);
        Route::put('/{id}', [CartController::class, 'update']);
        Route::delete('/{id}', [CartController::class, 'remove']);
        Route::post('/clear', [CartController::class, 'clear']);
    });

    Route::apiResource('orders', OrderController::class);
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
});
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->middleware(['signed'])
    ->name('verification.verify');


// Resend route (requires auth)
Route::post('/email/resend', [VerificationController::class, 'resend'])
    ->middleware('auth:sanctum')
    ->name('verification.resend');


Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
