<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Users;

/**
 * @OA\Tag(
 *     name="Notifications",
 *     description="API Endpoints for managing notifications"
 * )
 */
class NotificationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/notifications",
     *     summary="Get user notifications",
     *     tags={"Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="unread_only",
     *         in="query",
     *         description="Get only unread notifications",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(response=200, description="Notifications retrieved successfully")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            /** @var Users $user */
            $user = auth()->user();

            $query = $user->notifications();

            if ($request->has('unread_only') && $request->unread_only) {
                $query = $user->unreadNotifications();
            }

            $notifications = $query->paginate(20);

            return response()->json([
                'success' => true,
                'message' => 'Notifications retrieved successfully',
                'data' => $notifications,
                'unread_count' => $user->unreadNotifications()->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve notifications: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/notifications/{id}/read",
     *     summary="Mark notification as read",
     *     tags={"Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Notification marked as read")
     * )
     */
    public function markAsRead($id): JsonResponse
    {
        try {
            /** @var Users $user */
            $user = auth()->user();
            $notification = $user->notifications()->find($id);

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found'
                ], 404);
            }

            $notification->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/notifications/read-all",
     *     summary="Mark all notifications as read",
     *     tags={"Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="All notifications marked as read")
     * )
     */
    public function markAllAsRead(): JsonResponse
    {
        try {
            
            $user = auth()->user();
            $user->unreadNotifications->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all notifications as read: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/notifications/{id}",
     *     summary="Delete a notification",
     *     tags={"Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Notification deleted successfully")
     * )
     */
    public function destroy($id): JsonResponse
    {
        try {
            /** @var Users $user */
            $user = auth()->user();
            $notification = $user->notifications()->find($id);

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found'
                ], 404);
            }

            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/notifications",
     *     summary="Delete all notifications",
     *     tags={"Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="All notifications deleted")
     * )
     */
    public function destroyAll(): JsonResponse
    {
        try {
            /** @var Users $user */
            $user = auth()->user();
            $user->notifications()->delete();

            return response()->json([
                'success' => true,
                'message' => 'All notifications deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete notifications: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/notifications/unread-count",
     *     summary="Get unread notifications count",
     *     tags={"Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Unread count retrieved")
     * )
     */
    public function unreadCount(): JsonResponse
    {
        try {
            /** @var Users $user */
            $user = auth()->user();
            $count = $user->unreadNotifications()->count();

            return response()->json([
                'success' => true,
                'unread_count' => $count
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get unread count: ' . $e->getMessage()
            ], 500);
        }
    }
}
