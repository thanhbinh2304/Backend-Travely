<?php

namespace App\Docs\API;

class HistoryDocs
{
    /**
     * @OA\Get(
     *     path="/history",
     *     summary="Get user's booking history",
     *     description="Get all booking history for authenticated user",
     *     tags={"History"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by booking status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "confirmed", "cancelled", "completed"})
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Filter by start date",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Filter by end date",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-12-31")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="History retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="historyID", type="integer"),
     *                         @OA\Property(property="userID", type="string"),
     *                         @OA\Property(property="bookingID", type="integer"),
     *                         @OA\Property(property="action", type="string", example="Booking created"),
     *                         @OA\Property(property="actionDate", type="string", format="date-time"),
     *                         @OA\Property(
     *                             property="booking",
     *                             type="object",
     *                             @OA\Property(property="bookingID", type="integer"),
     *                             @OA\Property(property="status", type="string"),
     *                             @OA\Property(property="totalPrice", type="number"),
     *                             @OA\Property(property="tour", type="object")
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index() {}

    /**
     * @OA\Get(
     *     path="/history/{id}",
     *     summary="Get history entry details",
     *     description="Get specific history entry information",
     *     tags={"History"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="History ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="History entry retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="historyID", type="integer"),
     *                 @OA\Property(property="userID", type="string"),
     *                 @OA\Property(property="bookingID", type="integer"),
     *                 @OA\Property(property="action", type="string"),
     *                 @OA\Property(property="actionDate", type="string", format="date-time"),
     *                 @OA\Property(property="details", type="object"),
     *                 @OA\Property(property="booking", type="object"),
     *                 @OA\Property(property="user", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="History entry not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Can only view own history"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function show() {}

    /**
     * @OA\Get(
     *     path="/history/booking/{bookingId}",
     *     summary="Get history for specific booking",
     *     description="Get all history entries for a specific booking",
     *     tags={"History"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="bookingId",
     *         in="path",
     *         description="Booking ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Booking history retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="historyID", type="integer"),
     *                     @OA\Property(property="action", type="string"),
     *                     @OA\Property(property="actionDate", type="string", format="date-time"),
     *                     @OA\Property(property="details", type="object")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Can only view own booking history"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function byBooking() {}

    /**
     * @OA\Get(
     *     path="/admin/history",
     *     summary="Get all history entries (Admin)",
     *     description="Get all history entries in the system (Admin only)",
     *     tags={"History"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="Filter by user ID",
     *         required=false,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="action",
     *         in="query",
     *         description="Filter by action type",
     *         required=false,
     *         @OA\Schema(type="string", example="Booking created")
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Filter by start date",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Filter by end date",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=20)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="All history retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function adminIndex() {}

    /**
     * @OA\Get(
     *     path="/history/stats",
     *     summary="Get user history statistics",
     *     description="Get statistics about user's booking history",
     *     tags={"History"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Statistics retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="totalBookings", type="integer", example=15),
     *                 @OA\Property(property="completedBookings", type="integer", example=10),
     *                 @OA\Property(property="cancelledBookings", type="integer", example=2),
     *                 @OA\Property(property="pendingBookings", type="integer", example=3),
     *                 @OA\Property(property="totalSpent", type="number", example=4500.50),
     *                 @OA\Property(property="averageBookingValue", type="number", example=300.00)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function stats() {}
}
