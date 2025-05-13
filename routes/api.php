<?php

use App\Http\Controllers\UserAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/registration', [UserAuthController::class, 'register']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [UserAuthController::class, 'profile']);
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
