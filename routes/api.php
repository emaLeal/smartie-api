<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventsController;
use App\Http\Controllers\RafflesController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
        Route::middleware('auth:sanctum')->group(function() {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'profile']);
    });
});

Route::prefix('events')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/', [EventsController::class, 'index']);
        Route::get('/{id}', [EventsController::class, 'show']);
        Route::post('/', [EventsController::class, 'create']);
        Route::patch('/{id}', [EventsController::class, 'patch']);
        Route::delete('/{id}', [EventsController::class, 'delete']);
    });
});

Route::prefix('raffles')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/', [RafflesController::class, 'index']);
        Route::get('/{id}', [RafflesController::class, 'show']);
        Route::post('/', [RafflesController::class, 'create']);
        Route::patch('/{id}', [RafflesController::class, 'patch']);
        Route::delete('/{id}', [RafflesController::class, 'delete']);
    });
});
