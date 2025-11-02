<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Import controllers
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\LaunchController;
use App\Http\Controllers\Api\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Health check route
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'SpaceX Dashboard API is running',
        'timestamp' => now()->toISOString(),
    ]);
});

// Authentication routes (public)
Route::prefix('auth')->group(function () {
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/register', [RegisterController::class, 'register']);
    
    // Protected auth routes
    Route::middleware('jwt.auth')->group(function () {
        Route::post('/logout', [LoginController::class, 'logout']);
        Route::post('/refresh', [LoginController::class, 'refresh']);
    });
});

// Protected routes (require JWT authentication)
Route::middleware('jwt.auth')->group(function () {
    
    // Dashboard routes
    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index']);
        Route::get('/kpis', [DashboardController::class, 'kpis']);
        Route::get('/charts', [DashboardController::class, 'charts']);
    });

    // User profile routes
    Route::prefix('profile')->group(function () {
        Route::get('/', [DashboardController::class, 'profile']);
    });

    // Launch routes
    Route::prefix('launches')->group(function () {
        Route::get('/', [LaunchController::class, 'index']);
        Route::get('/upcoming', [LaunchController::class, 'upcoming']);
        Route::get('/stats', [LaunchController::class, 'stats']);
        Route::get('/by-year', [LaunchController::class, 'byYear']);
        Route::get('/available-years', [LaunchController::class, 'availableYears']);
        Route::get('/{spacexId}', [LaunchController::class, 'show']);
    });

    // Admin routes (require admin role)
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::post('/sync', [AdminController::class, 'syncData']);
        Route::post('/clear-cache', [AdminController::class, 'clearCache']);
        Route::get('/api-health', [AdminController::class, 'apiHealth']);
        Route::get('/stats', [AdminController::class, 'stats']);
    });
});