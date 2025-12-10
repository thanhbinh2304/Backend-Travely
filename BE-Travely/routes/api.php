<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StatisticController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\NotificationController;

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

// Public Review Routes
Route::get('/reviews', [ReviewController::class, 'index']);
Route::get('/reviews/{id}', [ReviewController::class, 'show']);

// Public Promotion Routes
Route::get('/promotions', [PromotionController::class, 'index']);
Route::get('/promotions/{id}', [PromotionController::class, 'show']);

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

    // User Invoice 
    Route::get('/invoices/{id}', [InvoiceController::class, 'show']);
    Route::get('/invoices/{id}/download', [InvoiceController::class, 'download']);

    // Payment Routes (User)
    Route::post('/payment/momo/create', [PaymentController::class, 'createMoMoPayment']);
    Route::post('/payment/vietqr/create', [PaymentController::class, 'createVietQRPayment']);
    Route::get('/payment/status/{checkoutID}', [PaymentController::class, 'getPaymentStatus']);
    Route::get('/payment/history', [PaymentController::class, 'getPaymentHistory']);

    // Wishlist Routes (User)
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{tourID}', [WishlistController::class, 'destroy']);
    Route::post('/wishlist/toggle/{tourID}', [WishlistController::class, 'toggle']);
    Route::get('/wishlist/share', [WishlistController::class, 'share']);
    Route::delete('/wishlist/clear', [WishlistController::class, 'clear']);

    // Review Routes (User)
    Route::get('/user/reviews', [ReviewController::class, 'myReviews']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
    Route::post('/reviews/{id}/images', [ReviewController::class, 'uploadImages']);

    // Notification Routes (User)
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    Route::delete('/notifications', [NotificationController::class, 'destroyAll']);
});

// Public Routes
Route::get('/wishlist/shared/{token}', [WishlistController::class, 'viewShared']);

// Public Payment Callback Routes (No auth required - MoMo will call these)
Route::post('/payment/momo/callback', [PaymentController::class, 'momoCallback']);
Route::get('/payment/momo/return', [PaymentController::class, 'momoReturn']);

// Admin Only Routes (role_id = 1)
Route::middleware(['auth:api', 'admin'])->group(function () {
    // User Management (Admin)
    Route::get('/admin/users', [UserController::class, 'index']);
    Route::get('/admin/users/{id}', [UserController::class, 'show']);
    Route::get('/admin/users/{userId}/bookings', [UserController::class, 'userBookings']);
    Route::patch('/admin/users/{id}/toggle-status', [UserController::class, 'toggleAccountStatus']);
    Route::delete('/admin/users/{id}', [UserController::class, 'destroy']);

    // Tour Management (Admin only)
    Route::get('/admin/tours', [TourController::class, 'index']); // Admin can get all tours
    Route::get('/admin/tours/{id}', [TourController::class, 'show']); // Admin can get tour detail
    Route::post('/tours/upload-image', [TourController::class, 'uploadImage']);
    Route::post('/tours', [TourController::class, 'store']);
    Route::post('/tours/{id}', [TourController::class, 'update']); // POST for multipart/form-data
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

    // Statistics & Analytics (Admin only)
    Route::get('/admin/statistics/dashboard', [StatisticController::class, 'dashboardOverview']);
    Route::get('/admin/statistics/bookings', [StatisticController::class, 'bookingStats']);
    Route::get('/admin/statistics/revenue', [StatisticController::class, 'revenueStats']);
    Route::get('/admin/statistics/payment-methods', [StatisticController::class, 'paymentMethodStats']);
    Route::get('/admin/statistics/top-tours', [StatisticController::class, 'topTours']);
    Route::get('/admin/statistics/tour-ratings', [StatisticController::class, 'tourRatings']);
    Route::get('/admin/statistics/user-growth', [StatisticController::class, 'userGrowth']);
    Route::get('/admin/statistics/financial-report', [StatisticController::class, 'financialReport']);

    // Payment Management (Admin only)
    Route::get('/admin/payments', [PaymentController::class, 'getAllPayments']);
    Route::get('/admin/payments/statistics', [PaymentController::class, 'getPaymentStatistics']);
    Route::get('/admin/payments/{id}', [PaymentController::class, 'getPaymentDetails']);
    Route::post('/admin/payments/{id}/refund', [PaymentController::class, 'refundPayment']);
    Route::post('/admin/payment/vietqr/verify', [PaymentController::class, 'verifyVietQRPayment']);

    // Review Management (Admin only)
    Route::get('/admin/reviews', [ReviewController::class, 'adminIndex']);
    Route::patch('/admin/reviews/{id}/approve', [ReviewController::class, 'approve']);
    Route::patch('/admin/reviews/{id}/hide', [ReviewController::class, 'hide']);
    Route::delete('/admin/reviews/{id}', [ReviewController::class, 'adminDestroy']);

    // Promotion Management (Admin only)
    Route::post('/promotions', [PromotionController::class, 'store']);
    Route::put('/promotions/{id}', [PromotionController::class, 'update']);
    Route::delete('/promotions/{id}', [PromotionController::class, 'destroy']);
});
