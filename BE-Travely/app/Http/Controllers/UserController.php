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
                ->where('bookingStatus', 'pending')->count(),
            'confirmed_bookings' => Booking::where('userID', $user->userID)
                ->where('bookingStatus', 'confirmed')->count(),
            'cancelled_bookings' => Booking::where('userID', $user->userID)
                ->where('bookingStatus', 'cancelled')->count(),
            'total_spent' => Booking::where('userID', $user->userID)
                ->whereIn('bookingStatus', ['confirmed', 'completed'])
                ->sum('totalPrice'),
        ];

        // Build response matching frontend expectations
        return response()->json([
            'success' => true,
            'data' => [
                'details' => [
                    'userID' => $user->userID,
                    'userName' => $user->userName,
                    'email' => $user->email,
                    'phoneNumber' => $user->phoneNumber,
                    'address' => $user->address,
                    'role_id' => $user->role_id,
                    'status' => $user->is_active ? 'active' : 'inactive',
                    'verified' => $user->email_verified,
                    'created_at' => $user->created_at->toISOString(),
                    'updated_at' => $user->updated_at->toISOString(),
                ],
                'roleName' => $user->role ? $user->role->role_name : ($user->is_admin ? 'Admin' : 'User'),
                'lastLogin' => $user->last_login ? $user->last_login->toISOString() : null,
                'stats' => $stats
            ]
        ], 200);
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
            // Toggle status: 1 (active) <-> 0 (inactive)
            $newStatus = $user->is_active ? 0 : 1;

            // Update only is_active field using query builder (doesn't trigger updated_at)
            DB::table('users')
                ->where('userID', $user->userID)
                ->update(['is_active' => $newStatus]);

            return response()->json([
                'success' => true,
                'message' => 'User account status updated successfully',
                'data' => [
                    'userID' => $user->userID,
                    'userName' => $user->userName,
                    'is_active' => $newStatus,
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
     * Delete user
     * Admin only
     */
    public function destroy($id)
    {
        $user = Users::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Prevent deleting own account
        try {
            $admin = JWTAuth::parseToken()->authenticate();
            if ($admin->userID === $user->userID) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete your own account'
                ], 400);
            }
        } catch (\Exception $e) {
            // Continue if token validation fails
        }

        try {
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
