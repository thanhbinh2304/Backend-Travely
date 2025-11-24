<?php

namespace App\Docs\API;

class BookingDocs
{
    // ==================== USER ENDPOINTS ====================

    /**
     * @OA\Get(
     *     path="/user/bookings",
     *     summary="Get user bookings",
     *     description="Get all bookings for authenticated user with filters",
     *     tags={"User - Bookings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="bookingStatus",
     *         in="query",
     *         description="Filter by booking status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "confirmed", "completed", "cancelled"})
     *     ),
     *     @OA\Parameter(
     *         name="paymentStatus",
     *         in="query",
     *         description="Filter by payment status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "paid", "refunded"})
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Filter from date",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Filter to date",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-12-31")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by tour name",
     *         required=false,
     *         @OA\Schema(type="string")
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
     *         description="Bookings retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="bookingID", type="integer"),
     *                     @OA\Property(property="userID", type="string"),
     *                     @OA\Property(property="tourID", type="integer"),
     *                     @OA\Property(property="bookingDate", type="string", format="date-time"),
     *                     @OA\Property(property="numberOfAdult", type="integer"),
     *                     @OA\Property(property="numberOfChild", type="integer"),
     *                     @OA\Property(property="totalPrice", type="number"),
     *                     @OA\Property(property="status", type="string", example="pending"),
     *                     @OA\Property(property="tour", type="object")
     *                 )
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
     *     path="/user/bookings/{id}",
     *     summary="Get booking details",
     *     description="Get specific booking information with full details",
     *     tags={"User - Bookings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"tourID","numberOfAdult"},
     *             @OA\Property(property="tourID", type="integer", example=1),
     *             @OA\Property(property="numberOfAdult", type="integer", example=2, minimum=1),
     *             @OA\Property(property="numberOfChild", type="integer", example=1, minimum=0),
     *             @OA\Property(property="specialRequests", type="string", example="Window seat preferred")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Booking created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Booking created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="bookingID", type="integer"),
     *                 @OA\Property(property="totalPrice", type="number"),
     *                 @OA\Property(property="status", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Tour not available or insufficient quantity"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function store() {}

    /**
     * @OA\Get(
     *     path="/bookings/{id}",
     *     summary="Get booking details",
     *     description="Get specific booking information",
     *     tags={"Bookings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Booking ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Booking retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="bookingID", type="integer"),
     *                 @OA\Property(property="tourID", type="integer"),
     *                 @OA\Property(property="numberOfAdult", type="integer"),
     *                 @OA\Property(property="numberOfChild", type="integer"),
     *                 @OA\Property(property="totalPrice", type="number"),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="tour", type="object"),
     *                 @OA\Property(property="invoice", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function show() {}

    /**
     * @OA\Patch(
     *     path="/bookings/{id}/cancel",
     *     summary="Cancel booking",
     *     description="Cancel a booking",
     *     tags={"Bookings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Booking ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Booking cancelled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Booking cancelled successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Booking cannot be cancelled"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found"
     *     )
     * )
     */
    public function cancel() {}

    /**
     * @OA\Get(
     *     path="/admin/bookings",
     *     summary="Get all bookings (Admin)",
     *     description="Get all bookings in the system (Admin only)",
     *     tags={"Bookings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "confirmed", "cancelled", "completed"})
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
     *         description="All bookings retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required"
     *     )
     * )
     */
    public function adminIndex() {}

    /**
     * @OA\Patch(
     *     path="/admin/bookings/{id}/status",
     *     summary="Update booking status (Admin)",
     *     description="Update booking status (Admin only)",
     *     tags={"Bookings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Booking ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"pending", "confirmed", "cancelled", "completed"}, example="confirmed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Booking status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Booking status updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required"
     *     )
     * )
     */
    public function updateStatus() {}
}
