<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Checkout;
use App\Models\Tour;
use App\Models\Review;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatisticController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/statistics/dashboard",
     *     summary="Get dashboard overview statistics",
     *     tags={"Statistics"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(response=200, description="Dashboard statistics")
     * )
     */
    public function dashboardOverview(Request $request)
    {
        try {
            $query = Booking::query();

            // Filter by date range
            if ($request->filled('start_date')) {
                $query->whereDate('bookingDate', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('bookingDate', '<=', $request->end_date);
            }

            $bookings = $query->get();

            // Total revenue (confirmed/completed & paid)
            $totalRevenue = $bookings
                ->whereIn('bookingStatus', ['confirmed', 'completed'])
                ->where('paymentStatus', 'paid')
                ->sum('totalPrice');

            // Booking status summary
            $bookingsSummary = [
                'total' => $bookings->count(),
                'pending' => $bookings->where('bookingStatus', 'pending')->count(),
                'confirmed' => $bookings->where('bookingStatus', 'confirmed')->count(),
                'completed' => $bookings->where('bookingStatus', 'completed')->count(),
                'cancelled' => $bookings->where('bookingStatus', 'cancelled')->count(),
            ];

            // Payment stats
            $paymentStats = Checkout::query()
                ->when($request->filled('start_date'), function ($q) use ($request) {
                    $q->whereDate('paymentDate', '>=', $request->start_date);
                })
                ->when($request->filled('end_date'), function ($q) use ($request) {
                    $q->whereDate('paymentDate', '<=', $request->end_date);
                })
                ->where('paymentStatus', 'completed')
                ->selectRaw('paymentMethod, COUNT(*) as count, SUM(amount) as total_amount')
                ->groupBy('paymentMethod')
                ->get();

            // Total tours & users
            $totalTours = Tour::count();
            $totalUsers = Users::where('role_id', 2)->count(); // Only regular users

            // New users in period
            $newUsers = Users::query()
                ->when($request->filled('start_date'), function ($q) use ($request) {
                    $q->whereDate('created_at', '>=', $request->start_date);
                })
                ->when($request->filled('end_date'), function ($q) use ($request) {
                    $q->whereDate('created_at', '<=', $request->end_date);
                })
                ->where('role_id', 2)
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'revenue' => [
                        'total' => $totalRevenue,
                        'by_payment_method' => $paymentStats
                    ],
                    'bookings' => $bookingsSummary,
                    'tours' => [
                        'total' => $totalTours
                    ],
                    'users' => [
                        'total' => $totalUsers,
                        'new' => $newUsers
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get dashboard overview',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/admin/statistics/bookings",
     *     summary="Get booking statistics with revenue breakdown",
     *     tags={"Statistics"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="start_date", in="query", @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="end_date", in="query", @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="group_by", in="query", @OA\Schema(type="string", enum={"day", "month", "year"})),
     *     @OA\Response(response=200, description="Booking statistics")
     * )
     */
    public function bookingStats(Request $request)
    {
        try {
            $query = Booking::query();

            // Filter by date range
            if ($request->filled('start_date')) {
                $query->whereDate('bookingDate', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('bookingDate', '<=', $request->end_date);
            }

            $bookings = $query->get();

            // Total revenue
            $totalRevenue = $bookings
                ->whereIn('bookingStatus', ['confirmed', 'completed'])
                ->where('paymentStatus', 'paid')
                ->sum('totalPrice');

            // Status summary
            $statusSummary = [
                'pending' => $bookings->where('bookingStatus', 'pending')->count(),
                'confirmed' => $bookings->where('bookingStatus', 'confirmed')->count(),
                'completed' => $bookings->where('bookingStatus', 'completed')->count(),
                'cancelled' => $bookings->where('bookingStatus', 'cancelled')->count(),
            ];

            // Group revenue by time period
            $groupBy = $request->get('group_by', 'day'); // day|month|year

            if ($groupBy === 'month') {
                $dateExpr = DB::raw("DATE_FORMAT(bookingDate, '%Y-%m-01')");
            } elseif ($groupBy === 'year') {
                $dateExpr = DB::raw("DATE_FORMAT(bookingDate, '%Y-01-01')");
            } else { // day
                $dateExpr = DB::raw("DATE(bookingDate)");
            }

            $revenueByPeriod = Booking::select(
                $dateExpr . ' as period',
                DB::raw("SUM(totalPrice) as total_revenue"),
                DB::raw("COUNT(*) as bookings_count")
            )
                ->whereIn('bookingStatus', ['confirmed', 'completed'])
                ->where('paymentStatus', 'paid')
                ->when($request->filled('start_date'), function ($q) use ($request) {
                    $q->whereDate('bookingDate', '>=', $request->start_date);
                })
                ->when($request->filled('end_date'), function ($q) use ($request) {
                    $q->whereDate('bookingDate', '<=', $request->end_date);
                })
                ->groupBy('period')
                ->orderBy('period')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_revenue' => $totalRevenue,
                    'status_summary' => $statusSummary,
                    'revenue_by_time' => $revenueByPeriod,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get booking stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/admin/statistics/revenue",
     *     summary="Get detailed revenue statistics",
     *     tags={"Statistics"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Revenue statistics")
     * )
     */
    public function revenueStats(Request $request)
    {
        try {
            $query = Booking::query();

            if ($request->filled('start_date')) {
                $query->whereDate('bookingDate', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('bookingDate', '<=', $request->end_date);
            }

            $bookings = $query->get();

            $revenue = [
                'total' => $bookings->whereIn('bookingStatus', ['confirmed', 'completed'])
                    ->where('paymentStatus', 'paid')
                    ->sum('totalPrice'),
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
            ];

            return response()->json([
                'success' => true,
                'data' => $revenue
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get revenue stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/admin/statistics/payment-methods",
     *     summary="Get payment method statistics",
     *     tags={"Statistics"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Payment method statistics")
     * )
     */
    public function paymentMethodStats(Request $request)
    {
        try {
            $query = Checkout::query()
                ->where('paymentStatus', 'completed');

            if ($request->filled('start_date')) {
                $query->whereDate('paymentDate', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('paymentDate', '<=', $request->end_date);
            }

            $stats = $query
                ->selectRaw('
                    paymentMethod,
                    COUNT(*) as total_transactions,
                    SUM(amount) as total_amount,
                    AVG(amount) as avg_amount,
                    MIN(amount) as min_amount,
                    MAX(amount) as max_amount
                ')
                ->groupBy('paymentMethod')
                ->get();

            $totalAmount = $stats->sum('total_amount');
            $totalTransactions = $stats->sum('total_transactions');

            $statsWithPercentage = $stats->map(function ($item) use ($totalAmount, $totalTransactions) {
                return [
                    'payment_method' => $item->paymentMethod,
                    'total_transactions' => $item->total_transactions,
                    'transaction_percentage' => $totalTransactions > 0
                        ? round(($item->total_transactions / $totalTransactions) * 100, 2)
                        : 0,
                    'total_amount' => $item->total_amount,
                    'amount_percentage' => $totalAmount > 0
                        ? round(($item->total_amount / $totalAmount) * 100, 2)
                        : 0,
                    'avg_amount' => round($item->avg_amount, 2),
                    'min_amount' => $item->min_amount,
                    'max_amount' => $item->max_amount,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => [
                        'total_transactions' => $totalTransactions,
                        'total_amount' => $totalAmount
                    ],
                    'by_method' => $statsWithPercentage
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get payment method stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/admin/statistics/top-tours",
     *     summary="Get top performing tours",
     *     tags={"Statistics"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="limit", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Top tours")
     * )
     */
    public function topTours(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);

            $query = Booking::join('tour', 'booking.tourID', '=', 'tour.tourID')
                ->whereIn('booking.bookingStatus', ['confirmed', 'completed'])
                ->where('booking.paymentStatus', 'paid');

            if ($request->filled('start_date')) {
                $query->whereDate('booking.bookingDate', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('booking.bookingDate', '<=', $request->end_date);
            }

            $tours = $query
                ->groupBy('tour.tourID', 'tour.tourName', 'tour.destination', 'tour.price')
                ->select(
                    'tour.tourID',
                    'tour.tourName',
                    'tour.destination',
                    'tour.price',
                    DB::raw('COUNT(booking.bookingID) as bookings_count'),
                    DB::raw('SUM(booking.totalPrice) as total_revenue'),
                    DB::raw('SUM(booking.numAdults + booking.numChildren) as total_guests')
                )
                ->orderByDesc('total_revenue')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $tours
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get top tours',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/admin/statistics/tour-ratings",
     *     summary="Get tour rating statistics",
     *     tags={"Statistics"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Tour rating statistics")
     * )
     */
    public function tourRatings(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);

            $query = Review::join('tour', 'review.tourID', '=', 'tour.tourID');

            if ($request->filled('start_date')) {
                $query->whereDate('review.timestamp', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('review.timestamp', '<=', $request->end_date);
            }

            $stats = $query
                ->groupBy('tour.tourID', 'tour.tourName', 'tour.destination')
                ->select(
                    'tour.tourID',
                    'tour.tourName',
                    'tour.destination',
                    DB::raw('AVG(review.rating) as avg_rating'),
                    DB::raw('COUNT(review.reviewID) as total_reviews'),
                    DB::raw('SUM(CASE WHEN review.rating = 5 THEN 1 ELSE 0 END) as five_star_count'),
                    DB::raw('SUM(CASE WHEN review.rating >= 4 THEN 1 ELSE 0 END) as four_plus_count')
                )
                ->orderByDesc('avg_rating')
                ->orderByDesc('total_reviews')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get rating stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/admin/statistics/user-growth",
     *     summary="Get user growth statistics",
     *     tags={"Statistics"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="User growth statistics")
     * )
     */
    public function userGrowth(Request $request)
    {
        try {
            $start = $request->get('start_date');
            $end = $request->get('end_date');

            if (!$start || !$end) {
                $end = Carbon::today();
                $start = $end->copy()->subDays(30);
            }

            // Total users
            $totalUsers = Users::where('role_id', 2)->count();

            // New users in period
            $newUsers = Users::whereBetween('created_at', [
                Carbon::parse($start)->startOfDay(),
                Carbon::parse($end)->endOfDay()
            ])->where('role_id', 2)->count();

            // Users by date
            $groupBy = $request->get('group_by', 'day');

            if ($groupBy === 'month') {
                $dateExpr = DB::raw("DATE_FORMAT(created_at, '%Y-%m-01')");
            } elseif ($groupBy === 'year') {
                $dateExpr = DB::raw("DATE_FORMAT(created_at, '%Y-01-01')");
            } else {
                $dateExpr = DB::raw("DATE(created_at)");
            }

            $usersByPeriod = Users::select(
                $dateExpr . ' as period',
                DB::raw('COUNT(*) as new_users')
            )
                ->whereBetween('created_at', [
                    Carbon::parse($start)->startOfDay(),
                    Carbon::parse($end)->endOfDay()
                ])
                ->where('role_id', 2)
                ->groupBy('period')
                ->orderBy('period')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'period' => [
                        'start_date' => $start,
                        'end_date' => $end
                    ],
                    'total_users' => $totalUsers,
                    'new_users' => $newUsers,
                    'users_by_period' => $usersByPeriod
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get user growth stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
