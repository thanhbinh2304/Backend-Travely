<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TourController;

/*
|--------------------------------------------------------------------------
| API Routes - JWT Version
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public Auth Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Social Login Routes
Route::post('/login/google', [AuthController::class, 'loginWithGoogle']);
Route::post('/login/facebook', [AuthController::class, 'loginWithFacebook']);

// JWT Refresh Token (can be accessed with expired token)
Route::post('/refresh', [AuthController::class, 'refresh']);

// Public Tour Routes (No authentication required)
Route::get('/tours', [TourController::class, 'index']);
Route::get('/tours/featured', [TourController::class, 'featured']);
Route::get('/tours/available', [TourController::class, 'available']);
Route::get('/tours/search', [TourController::class, 'search']);
Route::get('/tours/destination/{destination}', [TourController::class, 'byDestination']);
Route::get('/tours/{id}', [TourController::class, 'show']);

// Protected Routes (require JWT authentication)
Route::middleware('auth:api')->group(function () {
    // Auth routes - Available for all authenticated users
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::get('/is-admin', [AuthController::class, 'isAdmin']);
});

// User Routes (role_id = 2 or Admin role_id = 1)
Route::middleware(['auth:api', 'user'])->group(function () {
    // User can access these routes
    // Example: booking, wishlist, etc.
});

// Admin Only Routes (role_id = 1)
Route::middleware(['auth:api', 'admin'])->group(function () {
    // Only admin can access these routes
    Route::apiResource('users', UserController::class);

    // Tour Management (Admin only)
    Route::post('/tours', [TourController::class, 'store']);
    Route::put('/tours/{id}', [TourController::class, 'update']);
    Route::delete('/tours/{id}', [TourController::class, 'destroy']);
    Route::patch('/tours/{id}/availability', [TourController::class, 'updateAvailability']);
    Route::patch('/tours/{id}/quantity', [TourController::class, 'updateQuantity']);

    // Add more admin routes here
});
