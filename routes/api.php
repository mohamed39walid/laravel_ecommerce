<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Api\OrderController;




Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/categories',[CategoryController::class,'index']);
Route::get('/categories/{category}',[CategoryController::class,'show']);

Route::get('/products',[ProductController::class,'index']);
Route::get('/products/{id}',[ProductController::class,'show']);


//auth_sanctum middleware
Route::middleware(['auth:sanctum','admin'])->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/categories',[CategoryController::class,'store']);
    Route::put('/categories/{category}',[CategoryController::class,'update']);
    Route::delete('/categories/{category}',[CategoryController::class,'destroy']);
    // Route::apiResource('products', ProductController::class);

    Route::prefix('cart')->group(function () {
        Route::post('/', [CartController::class, 'add']);
        Route::get('/', [CartController::class, 'index']);
        Route::put('/{id}', [CartController::class, 'update']);
        Route::delete('/{id}', [CartController::class, 'remove']);
        Route::post('/clear', [CartController::class, 'clear']);
    });

    Route::apiResource('orders', OrderController::class);
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);


    Route::post('/products',[ProductController::class,'store']);
    Route::put('/products/{id}',[ProductController::class,'update']);
    Route::delete('/products/{id}',[ProductController::class,'destroy']);
});




//verifying email
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->middleware(['signed'])
    ->name('verification.verify');


// Resend route (requires auth)
Route::post('/email/resend', [VerificationController::class, 'resend'])
    ->middleware('auth:sanctum')
    ->name('verification.resend');

//google authentication routes
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
