<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Tour;
use App\Models\History;
use App\Models\Checkout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

class BookingController extends Controller
{
    // ==================== USER METHODS ====================

    /**
     * Get user's bookings
     * User access only
     */
    public function index(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $query = Booking::with(['tour.images', 'invoice'])
                ->where('userID', $user->userID);

            // Filter by status
            if ($request->has('bookingStatus')) {
                $query->where('bookingStatus', $request->bookingStatus);
            }

            // Filter by payment status
            if ($request->has('paymentStatus')) {
                $query->where('paymentStatus', $request->paymentStatus);
            }

            // Filter by date range
            if ($request->has('start_date')) {
                $query->where('bookingDate', '>=', $request->start_date);
            }
            if ($request->has('end_date')) {
                $query->where('bookingDate', '<=', $request->end_date);
            }

            // Search by tour name
            if ($request->has('search')) {
                $search = $request->search;
                $query->whereHas('tour', function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%");
                });
            }

            // Sort
            $sortBy = $request->get('sort_by', 'bookingDate');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $perPage = $request->get('per_page', 15);
            $bookings = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $bookings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch bookings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get booking details by ID
     * User access only
     */
    public function show($id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $booking = Booking::with([
                'tour.images',
                'tour.itineraries',
                'invoice',
                'checkout'
            ])->where('bookingID', $id)
                ->where('userID', $user->userID)
                ->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found or access denied'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $booking
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch booking details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel booking
     * User access only
     */
    public function cancel($id, Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $booking = Booking::where('bookingID', $id)
                ->where('userID', $user->userID)
                ->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found or access denied'
                ], 404);
            }

            // Check if booking can be cancelled
            if (in_array($booking->bookingStatus, ['cancelled', 'completed'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel this booking. Status: ' . $booking->bookingStatus
                ], 400);
            }

            // Check cancellation policy (e.g., must cancel 24h before booking date)
            $bookingDate = Carbon::parse($booking->bookingDate);
            $now = Carbon::now();
            $hoursDifference = $now->diffInHours($bookingDate, false);

            if ($hoursDifference < 24 && $hoursDifference > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking must be cancelled at least 24 hours before the tour date'
                ], 400);
            }

            // Update booking status
            $booking->bookingStatus = 'cancelled';
            $booking->save();

            // Log history
            History::create([
                'userID' => $user->userID,
                'bookingID' => $booking->bookingID,
                'action' => 'Booking cancelled',
                'actionDate' => now()
            ]);

            // Restore tour quantity
            $tour = Tour::find($booking->tourID);
            if ($tour) {
                $tour->quantity += ($booking->numAdults + $booking->numChildren);
                $tour->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Booking cancelled successfully',
                'data' => $booking->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ==================== CART METHODS ====================

    /**
     * Get user's cart (pending bookings)
     */
    public function getCart()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $cartItems = Booking::with(['tour.images', 'tour.reviews'])
                ->where('userID', $user->userID)
                ->where('paymentStatus', 'pending')
                ->where('bookingStatus', 'confirmed') // Only confirmed, not cancelled
                ->orderBy('bookingDate', 'desc')
                ->get();

            // Add calculated fields
            $cartItems->each(function ($item) {
                if ($item->tour) {
                    $item->tour->avg_rating = $item->tour->reviews->avg('rating');
                    $item->tour->review_count = $item->tour->reviews->count();
                }
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'items' => $cartItems,
                    'total' => $cartItems->count(),
                    'subtotal' => $cartItems->sum('totalPrice')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add to cart (create pending booking)
     */
    public function addToCart(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $validator = Validator::make($request->all(), [
                'tourID' => 'required|integer|exists:tour,tourID',
                'bookingDate' => 'required|date|after_or_equal:today',
                'numAdults' => 'required|integer|min:1',
                'numChildren' => 'required|integer|min:0',
                'specialRequests' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 400);
            }

            $tour = Tour::findOrFail($request->tourID);

            // Check availability
            if (!$tour->availability || $tour->quantity < ($request->numAdults + $request->numChildren)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tour không còn chỗ trống'
                ], 400);
            }

            // Calculate total price
            $totalPrice = ($request->numAdults * $tour->priceAdult) +
                ($request->numChildren * $tour->priceChild);

            // Check if already in cart (same tour, same date)
            $existingCart = Booking::where('userID', $user->userID)
                ->where('tourID', $request->tourID)
                ->where('bookingDate', $request->bookingDate)
                ->where('paymentStatus', 'pending')
                ->first();

            if ($existingCart) {
                // Update existing cart item
                $existingCart->numAdults = $request->numAdults;
                $existingCart->numChildren = $request->numChildren;
                $existingCart->totalPrice = $totalPrice;
                $existingCart->specialRequests = $request->specialRequests ?? '';
                $existingCart->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Cart updated successfully',
                    'data' => $existingCart->load('tour.images')
                ]);
            }

            // Create new cart item (pending booking)
            $booking = Booking::create([
                'tourID' => $request->tourID,
                'userID' => $user->userID,
                'bookingDate' => $request->bookingDate,
                'numAdults' => $request->numAdults,
                'numChildren' => $request->numChildren,
                'totalPrice' => $totalPrice,
                'paymentStatus' => 'pending',
                'bookingStatus' => 'confirmed',
                'specialRequests' => $request->specialRequests ?? ''
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Added to cart successfully',
                'data' => $booking->load('tour.images')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add to cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove from cart (delete pending booking)
     */
    public function removeFromCart($bookingID)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $booking = Booking::where('bookingID', $bookingID)
                ->where('userID', $user->userID)
                ->where('paymentStatus', 'pending')
                ->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart item not found'
                ], 404);
            }

            // Check if booking has checkout records
            $hasCheckout = \App\Models\Checkout::where('bookingID', $bookingID)->exists();

            if ($hasCheckout) {
                // If has checkout, just cancel the booking instead of deleting
                $booking->update([
                    'bookingStatus' => 'cancelled'
                ]);
            } else {
                // If no checkout, safe to delete
                $booking->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Removed from cart successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove from cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update cart item
     */
    public function updateCartItem($bookingID, Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $validator = Validator::make($request->all(), [
                'numAdults' => 'required|integer|min:1',
                'numChildren' => 'required|integer|min:0',
                'bookingDate' => 'required|date|after_or_equal:today',
                'specialRequests' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 400);
            }

            $booking = Booking::where('bookingID', $bookingID)
                ->where('userID', $user->userID)
                ->where('paymentStatus', 'pending')
                ->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart item not found'
                ], 404);
            }

            $tour = Tour::findOrFail($booking->tourID);

            // Check availability
            if (!$tour->availability || $tour->quantity < ($request->numAdults + $request->numChildren)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không đủ chỗ trống'
                ], 400);
            }

            // Calculate new total price
            $totalPrice = ($request->numAdults * $tour->priceAdult) +
                ($request->numChildren * $tour->priceChild);

            $booking->numAdults = $request->numAdults;
            $booking->numChildren = $request->numChildren;
            $booking->bookingDate = $request->bookingDate;
            $booking->totalPrice = $totalPrice;
            $booking->specialRequests = $request->specialRequests ?? '';
            $booking->save();

            return response()->json([
                'success' => true,
                'message' => 'Cart updated successfully',
                'data' => $booking->load('tour.images')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear cart (delete all pending bookings)
     */
    public function clearCart()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            Booking::where('userID', $user->userID)
                ->where('paymentStatus', 'pending')
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cart cleared successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ==================== ADMIN METHODS ====================

    /**
     * Get all bookings (Admin only)
     */
    public function adminIndex(Request $request)
    {
        try {
            $query = Booking::with(['user', 'tour', 'invoice']);

            // Filter by booking status
            if ($request->has('bookingStatus')) {
                $query->where('bookingStatus', $request->bookingStatus);
            }

            // Filter by payment status
            if ($request->has('paymentStatus')) {
                $query->where('paymentStatus', $request->paymentStatus);
            }

            // Filter by user
            if ($request->has('userID')) {
                $query->where('userID', $request->userID);
            }

            // Filter by tour
            if ($request->has('tourID')) {
                $query->where('tourID', $request->tourID);
            }

            // Filter by date range
            if ($request->has('start_date')) {
                $query->where('bookingDate', '>=', $request->start_date);
            }
            if ($request->has('end_date')) {
                $query->where('bookingDate', '<=', $request->end_date);
            }

            // Search by user name or email
            if ($request->has('search')) {
                $search = $request->search;
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('userName', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Sort
            $sortBy = $request->get('sort_by', 'bookingDate');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $perPage = $request->get('per_page', 20);
            $bookings = $query->paginate($perPage);

            // Add statistics
            $stats = [
                'total_bookings' => Booking::count(),
                'pending' => Booking::where('bookingStatus', 'pending')->count(),
                'confirmed' => Booking::where('bookingStatus', 'confirmed')->count(),
                'completed' => Booking::where('bookingStatus', 'completed')->count(),
                'cancelled' => Booking::where('bookingStatus', 'cancelled')->count(),
                'total_revenue' => Booking::whereIn('bookingStatus', ['confirmed', 'completed'])
                    ->where('paymentStatus', 'paid')
                    ->sum('totalPrice'),
            ];

            return response()->json([
                'success' => true,
                'data' => $bookings,
                'statistics' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch bookings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get booking detail (Admin only)
     */
    public function adminShow($id)
    {
        try {
            $booking = Booking::with(['user', 'tour', 'checkout', 'invoice'])->find($id);

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $booking
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch booking detail',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirm booking (Admin only)
     */
    public function confirmBooking($id)
    {
        try {
            $booking = Booking::with(['user', 'tour'])->find($id);

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found'
                ], 404);
            }

            if ($booking->bookingStatus !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending bookings can be confirmed. Current status: ' . $booking->bookingStatus
                ], 400);
            }

            // Update booking status
            $booking->bookingStatus = 'confirmed';
            $booking->save();

            // Send notification to user
            $booking->user->notify(new \App\Notifications\BookingConfirmedNotification($booking));

            // Log history
            $admin = JWTAuth::parseToken()->authenticate();
            History::create([
                'userID' => $booking->userID,
                'bookingID' => $booking->bookingID,
                'action' => 'Booking confirmed by admin',
                'actionDate' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking confirmed successfully',
                'data' => $booking->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject booking (Admin only)
     */
    public function rejectBooking($id, Request $request)
    {
        try {
            $booking = Booking::with(['user', 'tour'])->find($id);

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found'
                ], 404);
            }

            if ($booking->bookingStatus !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending bookings can be rejected'
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'reason' => 'required|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update booking status
            $booking->bookingStatus = 'cancelled';
            $booking->specialRequests = ($booking->specialRequests ? $booking->specialRequests . ' | ' : '')
                . 'Rejection reason: ' . $request->reason;
            $booking->save();

            // Restore tour quantity
            $tour = Tour::find($booking->tourID);
            if ($tour) {
                $tour->quantity += ($booking->numAdults + $booking->numChildren);
                $tour->save();
            }

            // Log history
            History::create([
                'userID' => $booking->userID,
                'bookingID' => $booking->bookingID,
                'action' => 'Booking rejected by admin: ' . $request->reason,
                'actionDate' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking rejected successfully',
                'data' => $booking->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update booking status (Admin only)
     */
    public function updateStatus($id, Request $request)
    {
        try {
            $booking = Booking::with(['user', 'tour'])->find($id);

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'bookingStatus' => 'required|in:pending,confirmed,completed,cancelled',
                'paymentStatus' => 'sometimes|in:pending,paid,refunded',
                'notes' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $oldStatus = $booking->bookingStatus;
            $newStatus = $request->bookingStatus;

            // Update status
            $booking->bookingStatus = $newStatus;

            if ($request->has('paymentStatus')) {
                $booking->paymentStatus = $request->paymentStatus;
            }

            if ($request->has('notes')) {
                $booking->specialRequests = ($booking->specialRequests ? $booking->specialRequests . ' | ' : '')
                    . 'Admin note: ' . $request->notes;
            }

            $booking->save();

            // Handle tour quantity
            if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                // Restore quantity when cancelled
                $tour = Tour::find($booking->tourID);
                if ($tour) {
                    $tour->quantity += ($booking->numAdults + $booking->numChildren);
                    $tour->save();
                }
            }

            // Log history
            $admin = JWTAuth::parseToken()->authenticate();
            History::create([
                'userID' => $booking->userID,
                'bookingID' => $booking->bookingID,
                'action' => "Status updated from {$oldStatus} to {$newStatus} by admin",
                'actionDate' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking status updated successfully',
                'data' => $booking->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update booking status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export booking report (Admin only)
     */
    public function exportReport(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'format' => 'sometimes|in:json,csv'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = Booking::with(['user', 'tour'])
                ->whereBetween('bookingDate', [$request->start_date, $request->end_date]);

            // Apply filters
            if ($request->has('bookingStatus')) {
                $query->where('bookingStatus', $request->bookingStatus);
            }
            if ($request->has('paymentStatus')) {
                $query->where('paymentStatus', $request->paymentStatus);
            }

            $bookings = $query->orderBy('bookingDate', 'desc')->get();

            // Calculate statistics
            $report = [
                'period' => [
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                ],
                'summary' => [
                    'total_bookings' => $bookings->count(),
                    'pending' => $bookings->where('bookingStatus', 'pending')->count(),
                    'confirmed' => $bookings->where('bookingStatus', 'confirmed')->count(),
                    'completed' => $bookings->where('bookingStatus', 'completed')->count(),
                    'cancelled' => $bookings->where('bookingStatus', 'cancelled')->count(),
                    'total_revenue' => $bookings->whereIn('bookingStatus', ['confirmed', 'completed'])
                        ->where('paymentStatus', 'paid')
                        ->sum('totalPrice'),
                    'total_guests' => $bookings->whereIn('bookingStatus', ['confirmed', 'completed'])
                        ->sum(function ($booking) {
                            return $booking->numAdults + $booking->numChildren;
                        }),
                ],
                'by_status' => [
                    'pending' => [
                        'count' => $bookings->where('bookingStatus', 'pending')->count(),
                        'revenue' => $bookings->where('bookingStatus', 'pending')->sum('totalPrice')
                    ],
                    'confirmed' => [
                        'count' => $bookings->where('bookingStatus', 'confirmed')->count(),
                        'revenue' => $bookings->where('bookingStatus', 'confirmed')
                            ->where('paymentStatus', 'paid')->sum('totalPrice')
                    ],
                    'completed' => [
                        'count' => $bookings->where('bookingStatus', 'completed')->count(),
                        'revenue' => $bookings->where('bookingStatus', 'completed')
                            ->where('paymentStatus', 'paid')->sum('totalPrice')
                    ],
                    'cancelled' => [
                        'count' => $bookings->where('bookingStatus', 'cancelled')->count(),
                        'refund' => $bookings->where('bookingStatus', 'cancelled')
                            ->where('paymentStatus', 'refunded')->sum('totalPrice')
                    ],
                ],
                'top_tours' => $bookings->groupBy('tourID')
                    ->map(function ($group) {
                        return [
                            'tour_id' => $group->first()->tourID,
                            'tour_name' => $group->first()->tour->title ?? 'N/A',
                            'bookings_count' => $group->count(),
                            'total_revenue' => $group->whereIn('bookingStatus', ['confirmed', 'completed'])
                                ->where('paymentStatus', 'paid')->sum('totalPrice'),
                        ];
                    })
                    ->sortByDesc('total_revenue')
                    ->values()
                    ->take(10),
                'bookings' => $bookings->map(function ($booking) {
                    return [
                        'bookingID' => $booking->bookingID,
                        'bookingDate' => $booking->bookingDate,
                        'user' => [
                            'userID' => $booking->user->userID ?? 'N/A',
                            'userName' => $booking->user->userName ?? 'N/A',
                            'email' => $booking->user->email ?? 'N/A',
                        ],
                        'tour' => [
                            'tourID' => $booking->tour->tourID ?? 'N/A',
                            'tourName' => $booking->tour->title ?? 'N/A',
                        ],
                        'guests' => [
                            'adults' => $booking->numAdults,
                            'children' => $booking->numChildren,
                            'total' => $booking->numAdults + $booking->numChildren,
                        ],
                        'totalPrice' => $booking->totalPrice,
                        'bookingStatus' => $booking->bookingStatus,
                        'paymentStatus' => $booking->paymentStatus,
                    ];
                }),
            ];

            // Return JSON format (CSV can be added later with a package like league/csv)
            return response()->json([
                'success' => true,
                'data' => $report,
                'generated_at' => now()->toDateTimeString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete booking (Admin only)
     */
    public function destroy($id)
    {
        try {
            $booking = Booking::find($id);

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found'
                ], 404);
            }

            // Check if booking has associated checkout/payment
            $checkout = Checkout::where('bookingID', $id)->first();
            if ($checkout && in_array($checkout->paymentStatus, ['paid', 'completed'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete booking with completed payment. Please refund first.'
                ], 400);
            }

            // Delete associated records
            if ($checkout) {
                $checkout->delete();
            }

            $booking->delete();

            return response()->json([
                'success' => true,
                'message' => 'Booking deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
