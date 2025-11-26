<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\InvoiceController;

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
Route::post('/auth/facebook/callback', [AuthController::class, 'facebookCallback']);

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
    // User Profile Management
    Route::put('/user/profile', [UserController::class, 'updateProfile']);
    Route::post('/user/change-password', [UserController::class, 'changePassword']);

    // User Activity History
    Route::get('/user/activity-history', [UserController::class, 'activityHistory']);

    // User Booking Management
    Route::get('/user/bookings', [BookingController::class, 'index']);
    Route::get('/user/bookings/{id}', [BookingController::class, 'show']);
    Route::patch('/user/bookings/{id}/cancel', [BookingController::class, 'cancel']);

    // Invoice - lịch sử thanh toán
    Route::get('/invoices', [InvoiceController::class, 'index']);
});

// Admin Only Routes (role_id = 1)
Route::middleware(['auth:api', 'admin'])->group(function () {
    // User Management (Admin)
    Route::get('/admin/users', [UserController::class, 'index']);
    Route::get('/admin/users/{id}', [UserController::class, 'show']);
    Route::get('/admin/users/{userId}/bookings', [UserController::class, 'userBookings']);
    Route::patch('/admin/users/{id}/toggle-status', [UserController::class, 'toggleAccountStatus']);

    // Tour Management (Admin only)
    Route::post('/tours', [TourController::class, 'store']);
    Route::put('/tours/{id}', [TourController::class, 'update']);
    Route::delete('/tours/{id}', [TourController::class, 'destroy']);
    Route::patch('/tours/{id}/availability', [TourController::class, 'updateAvailability']);
    Route::patch('/tours/{id}/quantity', [TourController::class, 'updateQuantity']);

    // Booking Management (Admin only)
    Route::get('/admin/bookings', [BookingController::class, 'adminIndex']);
    Route::patch('/admin/bookings/{id}/confirm', [BookingController::class, 'confirmBooking']);
    Route::patch('/admin/bookings/{id}/reject', [BookingController::class, 'rejectBooking']);
    Route::patch('/admin/bookings/{id}/status', [BookingController::class, 'updateStatus']);
    Route::get('/admin/bookings/export', [BookingController::class, 'exportReport']);

    // Dashboard - Booking & Revenue stats
    Route::get('/admin/stats/bookings', [BookingController::class, 'dashboardStats']);

    // Dashboard - Top tour & rating
    Route::get('/admin/stats/top-tours', [TourController::class, 'topTours']);
    Route::get('/admin/stats/ratings', [TourController::class, 'ratingStats']);

    // Dashboard - User stats
    Route::get('/admin/stats/new-users', [UserController::class, 'newUsersStats']);
});

// Invoice (User) - xem lịch sử thanh toán, chi tiết, tải hóa đơn
    Route::middleware(['auth:api', 'user'])->group(function () {
        Route::get('/invoices', [InvoiceController::class, 'index']);
        Route::get('/invoices/{id}', [InvoiceController::class, 'show']);
        Route::get('/invoices/{id}/download', [InvoiceController::class, 'download']);
});
    // Admin
    Route::middleware(['auth:api', 'admin'])->group(function () {
    // Route::get('/admin/invoices', [InvoiceController::class, 'adminIndex']);
    // Route::post('/admin/invoices/{id}/refund', [InvoiceController::class, 'refund']);
});
