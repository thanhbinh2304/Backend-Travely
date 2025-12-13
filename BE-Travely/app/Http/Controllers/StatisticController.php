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

            // Calculate monthly comparisons
            $lastMonth = now()->subMonth();
            $bookingsThisMonth = Booking::whereYear('bookingDate', now()->year)
                ->whereMonth('bookingDate', now()->month)
                ->count();

            $revenueThisMonth = Booking::whereYear('bookingDate', now()->year)
                ->whereMonth('bookingDate', now()->month)
                ->whereIn('bookingStatus', ['confirmed', 'completed'])
                ->where('paymentStatus', 'paid')
                ->sum('totalPrice');

            return response()->json([
                'success' => true,
                'data' => [
                    // Flat structure for frontend
                    'total_users' => $totalUsers,
                    'total_tours' => $totalTours,
                    'total_bookings' => $bookingsSummary['total'],
                    'total_revenue' => $totalRevenue,
                    'bookings_this_month' => $bookingsThisMonth,
                    'revenue_this_month' => $revenueThisMonth,
                    'new_users' => $newUsers,

                    // Detailed breakdowns (optional, for future use)
                    'bookings_summary' => $bookingsSummary,
                    'payment_methods' => $paymentStats,
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
            $pending = $bookings->where('bookingStatus', 'pending')->count();
            $confirmed = $bookings->where('bookingStatus', 'confirmed')->count();
            $completed = $bookings->where('bookingStatus', 'completed')->count();
            $cancelled = $bookings->where('bookingStatus', 'cancelled')->count();

            // Calculate average booking value
            $paidBookings = $bookings->whereIn('bookingStatus', ['confirmed', 'completed'])
                ->where('paymentStatus', 'paid');
            $avgBookingValue = $paidBookings->count() > 0
                ? $paidBookings->avg('totalPrice')
                : 0;

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
                    // Flat structure matching frontend
                    'total_bookings' => $bookings->count(),
                    'pending' => $pending,
                    'confirmed' => $confirmed,
                    'completed' => $completed,
                    'cancelled' => $cancelled,
                    'total_revenue' => $totalRevenue,
                    'average_booking_value' => round($avgBookingValue, 0),
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

            $totalRevenue = $bookings->whereIn('bookingStatus', ['confirmed', 'completed'])
                ->where('paymentStatus', 'paid')
                ->sum('totalPrice');

            // Revenue by period (for charts)
            $groupBy = $request->get('period', 'month');
            if ($groupBy === 'day') {
                $dateExpr = DB::raw("DATE(bookingDate)");
            } elseif ($groupBy === 'week') {
                $dateExpr = DB::raw("DATE_FORMAT(bookingDate, '%Y-%u')"); // Year-Week
            } elseif ($groupBy === 'year') {
                $dateExpr = DB::raw("YEAR(bookingDate)");
            } else { // month (default)
                $dateExpr = DB::raw("DATE_FORMAT(bookingDate, '%Y-%m')");
            }

            $revenueByPeriod = Booking::select(
                $dateExpr . ' as period',
                DB::raw("SUM(totalPrice) as revenue")
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

            // Revenue by tour
            $revenueByTour = Booking::join('tour', 'booking.tourID', '=', 'tour.tourID')
                ->whereIn('booking.bookingStatus', ['confirmed', 'completed'])
                ->where('booking.paymentStatus', 'paid')
                ->when($request->filled('start_date'), function ($q) use ($request) {
                    $q->whereDate('booking.bookingDate', '>=', $request->start_date);
                })
                ->when($request->filled('end_date'), function ($q) use ($request) {
                    $q->whereDate('booking.bookingDate', '<=', $request->end_date);
                })
                ->groupBy('tour.tourID', 'tour.tourName')
                ->select(
                    'tour.tourID',
                    'tour.tourName',
                    DB::raw('SUM(booking.totalPrice) as revenue'),
                    DB::raw('COUNT(booking.bookingID) as bookings')
                )
                ->orderByDesc('revenue')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_revenue' => $totalRevenue,
                    'revenue_by_period' => $revenueByPeriod,
                    'revenue_by_tour' => $revenueByTour,
                ]
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
                    'tour.tourName as title',  // Renamed to match frontend
                    'tour.destination',
                    'tour.price',
                    DB::raw('COUNT(booking.bookingID) as total_bookings'),  // Renamed to match frontend
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

    /**
     * @OA\Get(
     *     path="/api/admin/statistics/financial-report",
     *     summary="Export financial report (Admin only)",
     *     tags={"Statistics"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Start date (Y-m-d)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="End date (Y-m-d)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="Export format: json or csv",
     *         required=false,
     *         @OA\Schema(type="string", enum={"json", "csv"}, default="json")
     *     ),
     *     @OA\Response(response=200, description="Financial report generated")
     * )
     */
    public function financialReport(Request $request)
    {
        try {
            $startDate = $request->filled('start_date')
                ? Carbon::parse($request->start_date)->startOfDay()
                : Carbon::now()->startOfMonth();

            $endDate = $request->filled('end_date')
                ? Carbon::parse($request->end_date)->endOfDay()
                : Carbon::now()->endOfDay();

            // Get payment transactions
            $payments = Checkout::with(['booking.tour', 'booking.user'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            // Build report using separate methods
            $report = [
                'report_info' => $this->getReportInfo($startDate, $endDate),
                'summary' => $this->getFinancialSummary($payments),
                'breakdown' => $this->getFinancialBreakdown($payments, $startDate, $endDate),
                'trends' => $this->getFinancialTrends($payments),
                'top_performers' => $this->getTopPerformers($startDate, $endDate),
                'refunds' => $this->getRefundStatistics($payments),
                'transactions' => $this->getTransactionDetails($payments)
            ];

            // Handle CSV export
            if ($request->get('format') === 'csv') {
                return $this->exportFinancialReportToCsv($report, $startDate, $endDate);
            }

            return response()->json([
                'success' => true,
                'message' => 'Financial report generated successfully',
                'data' => $report
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate financial report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get report information
     */
    private function getReportInfo($startDate, $endDate)
    {
        return [
            'generated_at' => now()->toDateTimeString(),
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'days' => $startDate->diffInDays($endDate) + 1
            ]
        ];
    }

    /**
     * Calculate financial summary
     */
    private function getFinancialSummary($payments)
    {
        $totalRevenue = $payments->where('paymentStatus', 'completed')->sum('amount');
        $totalRefunded = $payments->where('paymentStatus', 'refunded')->sum('refundAmount');
        $completedPayments = $payments->where('paymentStatus', 'completed');

        return [
            'total_transactions' => $payments->count(),
            'total_revenue' => $totalRevenue,
            'total_refunded' => $totalRefunded,
            'net_revenue' => $totalRevenue - $totalRefunded,
            'average_transaction' => $completedPayments->avg('amount'),
            'success_rate' => $payments->count() > 0
                ? round(($completedPayments->count() / $payments->count()) * 100, 2)
                : 0
        ];
    }

    /**
     * Get financial breakdown by payment method and status
     */
    private function getFinancialBreakdown($payments, $startDate, $endDate)
    {
        // Payment method breakdown
        $paymentMethodStats = Checkout::query()
            ->where('paymentStatus', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
                paymentMethod,
                COUNT(*) as total_transactions,
                SUM(amount) as total_amount,
                AVG(amount) as avg_amount
            ')
            ->groupBy('paymentMethod')
            ->get()
            ->map(function ($item) {
                return [
                    'payment_method' => $item->paymentMethod,
                    'count' => $item->total_transactions,
                    'total' => $item->total_amount,
                    'average' => $item->avg_amount
                ];
            });

        // Status breakdown
        $transactionsByStatus = $payments->groupBy('paymentStatus')
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total' => $group->sum('amount')
                ];
            });

        return [
            'by_payment_method' => $paymentMethodStats,
            'by_status' => $transactionsByStatus
        ];
    }

    /**
     * Get financial trends (daily revenue)
     */
    private function getFinancialTrends($payments)
    {
        $dailyRevenue = $payments->where('paymentStatus', 'completed')
            ->groupBy(function ($payment) {
                return Carbon::parse($payment->paymentDate)->format('Y-m-d');
            })
            ->map(function ($group, $date) {
                return [
                    'date' => $date,
                    'count' => $group->count(),
                    'total' => $group->sum('amount')
                ];
            })
            ->sortBy('date')
            ->values();

        return [
            'daily_revenue' => $dailyRevenue
        ];
    }

    /**
     * Get top performing tours
     */
    private function getTopPerformers($startDate, $endDate)
    {
        $topTours = Booking::with('tour')
            ->whereBetween('bookingDate', [$startDate, $endDate])
            ->whereIn('bookingStatus', ['confirmed', 'completed'])
            ->where('paymentStatus', 'paid')
            ->get()
            ->groupBy('tourID')
            ->map(function ($bookings) {
                $tour = $bookings->first()->tour;
                return [
                    'tour_id' => $tour->tourID ?? null,
                    'tour_title' => $tour->title ?? 'N/A',
                    'bookings_count' => $bookings->count(),
                    'total_revenue' => $bookings->sum('totalPrice')
                ];
            })
            ->sortByDesc('total_revenue')
            ->take(10)
            ->values();

        return [
            'tours' => $topTours
        ];
    }

    /**
     * Get refund statistics
     */
    private function getRefundStatistics($payments)
    {
        $refundedPayments = $payments->where('paymentStatus', 'refunded');
        $totalRefunded = $refundedPayments->sum('refundAmount');

        return [
            'total_refunded_amount' => $totalRefunded,
            'total_refunds' => $refundedPayments->count(),
            'refund_rate' => $payments->count() > 0
                ? round(($refundedPayments->count() / $payments->count()) * 100, 2)
                : 0
        ];
    }

    /**
     * Get detailed transaction list
     */
    private function getTransactionDetails($payments)
    {
        return $payments->map(function ($payment) {
            return [
                'checkout_id' => $payment->checkoutID,
                'booking_id' => $payment->bookingID,
                'transaction_id' => $payment->transactionID,
                'customer_name' => $payment->booking->user->userName ?? 'N/A',
                'tour_title' => $payment->booking->tour->title ?? 'N/A',
                'amount' => $payment->amount,
                'payment_method' => $payment->paymentMethod,
                'payment_status' => $payment->paymentStatus,
                'payment_date' => $payment->paymentDate,
                'refund_amount' => $payment->refundAmount,
                'refund_date' => $payment->refundDate,
                'refund_reason' => $payment->refundReason
            ];
        });
    }

    /**
     * Export financial report to CSV
     */
    private function exportFinancialReportToCsv($report, $startDate, $endDate)
    {
        $filename = 'financial_report_' . $startDate->format('Ymd') . '_' . $endDate->format('Ymd') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($report) {
            $file = fopen('php://output', 'w');

            // Summary section
            fputcsv($file, ['FINANCIAL REPORT SUMMARY']);
            fputcsv($file, ['Period', $report['report_info']['period']['start_date'] . ' to ' . $report['report_info']['period']['end_date']]);
            fputcsv($file, ['Generated At', $report['report_info']['generated_at']]);
            fputcsv($file, []);

            fputcsv($file, ['Total Transactions', $report['summary']['total_transactions']]);
            fputcsv($file, ['Total Revenue', number_format($report['summary']['total_revenue'], 0)]);
            fputcsv($file, ['Total Refunded', number_format($report['summary']['total_refunded'], 0)]);
            fputcsv($file, ['Net Revenue', number_format($report['summary']['net_revenue'], 0)]);
            fputcsv($file, ['Average Transaction', number_format($report['summary']['average_transaction'], 0)]);
            fputcsv($file, ['Success Rate', $report['summary']['success_rate'] . '%']);
            fputcsv($file, []);

            // Transactions detail
            fputcsv($file, ['TRANSACTION DETAILS']);
            fputcsv($file, ['Checkout ID', 'Booking ID', 'Transaction ID', 'Customer', 'Tour', 'Amount', 'Payment Method', 'Status', 'Payment Date', 'Refund Amount', 'Refund Date', 'Refund Reason']);

            foreach ($report['transactions'] as $transaction) {
                fputcsv($file, [
                    $transaction['checkout_id'],
                    $transaction['booking_id'],
                    $transaction['transaction_id'],
                    $transaction['customer_name'],
                    $transaction['tour_title'],
                    $transaction['amount'],
                    $transaction['payment_method'],
                    $transaction['payment_status'],
                    $transaction['payment_date'],
                    $transaction['refund_amount'] ?? '',
                    $transaction['refund_date'] ?? '',
                    $transaction['refund_reason'] ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
