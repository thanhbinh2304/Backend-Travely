<?php

namespace App\Docs\API;

class UserDocs
{
    // ==================== USER ENDPOINTS ====================

    /**
     * @OA\Put(
     *     path="/user/profile",
     *     summary="Update user profile",
     *     description="Update authenticated user's profile information",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="userName", type="string", example="johndoe", maxLength=32),
     *                 @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                 @OA\Property(property="phoneNumber", type="string", example="+1234567890", maxLength=15),
     *                 @OA\Property(property="address", type="string", example="123 Main St, City", maxLength=255),
     *                 @OA\Property(property="avatar", type="string", format="binary", description="Avatar image file (jpeg, png, jpg, gif, max 2MB)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Profile updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="userID", type="string"),
     *                 @OA\Property(property="userName", type="string"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="phoneNumber", type="string"),
     *                 @OA\Property(property="address", type="string"),
     *                 @OA\Property(property="avatar", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function updateProfile() {}

    /**
     * @OA\Post(
     *     path="/user/change-password",
     *     summary="Change user password",
     *     description="Change password for authenticated user",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password","new_password","new_password_confirmation"},
     *             @OA\Property(property="current_password", type="string", format="password", example="oldpassword123"),
     *             @OA\Property(property="new_password", type="string", format="password", example="newpassword123", minLength=6),
     *             @OA\Property(property="new_password_confirmation", type="string", format="password", example="newpassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password changed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password changed successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="token", type="string", description="New JWT token"),
     *                 @OA\Property(property="token_type", type="string", example="bearer"),
     *                 @OA\Property(property="expires_in", type="integer", example=3600)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Current password is incorrect"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function changePassword() {}

    /**
     * @OA\Get(
     *     path="/user/activity-history",
     *     summary="Get user activity history",
     *     description="Get authenticated user's activity history with filters",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="action",
     *         in="query",
     *         description="Filter by action type",
     *         required=false,
     *         @OA\Schema(type="string", example="Booking")
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
     *         name="sort_by",
     *         in="query",
     *         description="Sort field",
     *         required=false,
     *         @OA\Schema(type="string", example="actionDate")
     *     ),
     *     @OA\Parameter(
     *         name="sort_order",
     *         in="query",
     *         description="Sort order (asc or desc)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, example="desc")
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
     *         description="Activity history retrieved successfully",
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
     *                         @OA\Property(property="booking", type="object")
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
    public function activityHistory() {}

    // ==================== ADMIN ENDPOINTS ====================

    /**
     * @OA\Get(
     *     path="/admin/users",
     *     summary="Get all users (Admin)",
     *     description="Get list of all users with filters and pagination (Admin only)",
     *     tags={"Admin - User Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="role_id",
     *         in="query",
     *         description="Filter by role",
     *         required=false,
     *         @OA\Schema(type="integer", enum={1, 2}, example=2)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by username or email",
     *         required=false,
     *         @OA\Schema(type="string", example="john")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by account status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active", "locked"}, example="active")
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Filter by created date (start)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Filter by created date (end)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-12-31")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sort field",
     *         required=false,
     *         @OA\Schema(type="string", example="created_at")
     *     ),
     *     @OA\Parameter(
     *         name="sort_order",
     *         in="query",
     *         description="Sort order",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, example="desc")
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
     *         description="Users retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required"
     *     )
     * )
     */
    public function index() {}

    /**
     * @OA\Get(
     *     path="/admin/users/{id}",
     *     summary="Get user details by ID (Admin)",
     *     description="Get detailed user information with statistics (Admin only)",
     *     tags={"Admin - User Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="string", example="US000001")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="userID", type="string"),
     *                 @OA\Property(property="userName", type="string"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="phoneNumber", type="string"),
     *                 @OA\Property(property="address", type="string"),
     *                 @OA\Property(property="avatar", type="string"),
     *                 @OA\Property(property="role_id", type="integer"),
     *                 @OA\Property(property="is_locked", type="boolean"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="statistics", type="object",
     *                     @OA\Property(property="total_bookings", type="integer"),
     *                     @OA\Property(property="completed_bookings", type="integer"),
     *                     @OA\Property(property="cancelled_bookings", type="integer"),
     *                     @OA\Property(property="total_spent", type="number", format="float"),
     *                     @OA\Property(property="total_reviews", type="integer")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required"
     *     )
     * )
     */
    public function show() {}

    /**
     * @OA\Get(
     *     path="/admin/users/{userId}/bookings",
     *     summary="Get user bookings (Admin)",
     *     description="Get all bookings of a specific user with details (Admin only)",
     *     tags={"Admin - User Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="string", example="US000001")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by booking status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "confirmed", "completed", "cancelled"})
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Filter by booking date (start)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Filter by booking date (end)",
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
     *         description="User bookings retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="userID", type="string"),
     *                     @OA\Property(property="userName", type="string"),
     *                     @OA\Property(property="email", type="string")
     *                 ),
     *                 @OA\Property(property="bookings", type="object",
     *                     @OA\Property(property="data", type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="bookingID", type="integer"),
     *                             @OA\Property(property="tourID", type="integer"),
     *                             @OA\Property(property="startDate", type="string", format="date"),
     *                             @OA\Property(property="status", type="string"),
     *                             @OA\Property(property="totalPrice", type="number"),
     *                             @OA\Property(property="tour", type="object")
     *                         )
     *                     ),
     *                     @OA\Property(property="total", type="integer")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required"
     *     )
     * )
     */
    public function userBookings() {}

    /**
     * @OA\Patch(
     *     path="/admin/users/{id}/toggle-status",
     *     summary="Toggle user account status (Admin)",
     *     description="Lock or unlock user account (Admin only)",
     *     tags={"Admin - User Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="string", example="US000001")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="reason", type="string", example="Violation of terms of service", description="Reason for locking account (optional)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Account status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Account status updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="userID", type="string"),
     *                 @OA\Property(property="userName", type="string"),
     *                 @OA\Property(property="is_locked", type="boolean"),
     *                 @OA\Property(property="locked_reason", type="string", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Cannot lock admin account"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required"
     *     )
     * )
     */
    public function toggleAccountStatus() {}
}
