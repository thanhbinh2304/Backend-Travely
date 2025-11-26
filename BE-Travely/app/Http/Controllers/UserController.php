<?php

namespace App\Http\Controllers;

use App\Models\Users;
use App\Models\Booking;
use App\Models\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

class UserController extends Controller
{
    // ==================== USER METHODS ====================

    /**
     * Update user profile information
     * User access only
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'userName' => 'sometimes|string|max:32|unique:users,userName,' . $user->userID . ',userID',
                'email' => 'sometimes|email|max:255|unique:users,email,' . $user->userID . ',userID',
                'phoneNumber' => 'nullable|string|max:15',
                'address' => 'nullable|string|max:255',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Handle avatar upload if provided
            if ($request->hasFile('avatar')) {
                // Delete old avatar if exists
                if ($user->avatar && Storage::exists($user->avatar)) {
                    Storage::delete($user->avatar);
                }

                // Store new avatar
                $path = $request->file('avatar')->store('avatars', 'public');
                $user->avatar = $path;
            }

            // Update only provided fields
            $user->update($request->only(['userName', 'email', 'phoneNumber', 'address']));

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $user->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change user password
     * User access only
     */
    public function changePassword(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if current password is correct
            if (!Hash::check($request->current_password, $user->passWord)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 400);
            }

            // Update password (will be bcrypted by model mutator)
            $user->passWord = $request->new_password;
            $user->save();

            // Invalidate old token and generate new one
            JWTAuth::invalidate(JWTAuth::getToken());
            $newToken = JWTAuth::fromUser($user);

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully',
                'data' => [
                    'access_token' => $newToken
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to change password',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's activity history
     * User access only
     */
    public function activityHistory(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $query = History::with(['booking.tour'])
                ->where('userID', $user->userID);

            // Filter by action type
            if ($request->has('action')) {
                $query->where('action', 'like', '%' . $request->action . '%');
            }

            // Filter by date range
            if ($request->has('start_date')) {
                $query->where('actionDate', '>=', $request->start_date);
            }
            if ($request->has('end_date')) {
                $query->where('actionDate', '<=', $request->end_date);
            }

            $sortBy = $request->get('sort_by', 'actionDate');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $perPage = $request->get('per_page', 20);
            $history = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $history
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch activity history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ==================== ADMIN METHODS ====================

    /**
     * Get all users with filters and pagination
     * Admin only
     */
    public function index(Request $request)
    {
        $query = Users::with(['role']);

        // Filter by role
        if ($request->has('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('userName', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by account status (if you have this field)
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Filter by created date
        if ($request->has('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('created_at', '<=', $request->end_date);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $users = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Get user by ID with full details
     * Admin only
     */
    public function show($id)
    {
        $user = Users::with(['role'])->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Get user statistics
        $stats = [
            'total_bookings' => Booking::where('userID', $user->userID)->count(),
            'pending_bookings' => Booking::where('userID', $user->userID)
                ->where('status', 'pending')->count(),
            'confirmed_bookings' => Booking::where('userID', $user->userID)
                ->where('status', 'confirmed')->count(),
            'cancelled_bookings' => Booking::where('userID', $user->userID)
                ->where('status', 'cancelled')->count(),
            'total_spent' => Booking::where('userID', $user->userID)
                ->whereIn('status', ['confirmed', 'completed'])
                ->sum('totalPrice'),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'stats' => $stats
            ]
        ]);
    }

    /**
     * Get user's booking history
     * Admin only
     */
    public function userBookings($userId, Request $request)
    {
        $user = Users::find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $query = Booking::with(['tour.images', 'invoice'])
            ->where('userID', $userId);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('bookingDate', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('bookingDate', '<=', $request->end_date);
        }

        $perPage = $request->get('per_page', 10);
        $bookings = $query->orderBy('bookingDate', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'userID' => $user->userID,
                    'userName' => $user->userName,
                    'email' => $user->email,
                ],
                'bookings' => $bookings
            ]
        ]);
    }

    /**
     * Lock/Unlock user account
     * Admin only
     */
    public function toggleAccountStatus($id)
    {
        $user = Users::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Prevent locking own account
        try {
            $admin = JWTAuth::parseToken()->authenticate();
            if ($admin->userID === $user->userID) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot lock/unlock your own account'
                ], 400);
            }
        } catch (\Exception $e) {
            // Continue if token validation fails
        }

        try {
            // Toggle status (assuming you have a 'status' or 'is_active' field)
            // If you don't have this field, you can add it to the users table
            // For now, I'll use a hypothetical 'status' field

            // Option 1: If you have a 'status' field (active/locked)
            $user->is_active = ($user->is_active === true) ? false : true;

            // Option 2: If you have an 'is_active' boolean field
            // $user->is_active = !$user->is_active;

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'User account status updated successfully',
                'data' => [
                    'userID' => $user->userID,
                    'userName' => $user->userName,
                    'is_active' => $user->is_active,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update account status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * GET /admin/stats/new-users
     * -> Số lượng user mới trong khoảng thời gian
     *    Nếu không truyền ngày, mặc định 30 ngày gần nhất
     */
    public function newUsersStats(Request $request)
    {
        try {
            $start = $request->get('start_date');
            $end   = $request->get('end_date');

            if (!$start || !$end) {
                $end   = Carbon::today();
                $start = $end->copy()->subDays(30);
            }

            $count = Users::whereBetween('created_at', [
                Carbon::parse($start)->startOfDay(),
                Carbon::parse($end)->endOfDay()
            ])->count();

            return response()->json([
                'success' => true,
                'data'    => [
                    'start_date' => $start,
                    'end_date'   => $end,
                    'new_users'  => $count,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get new users stats',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
