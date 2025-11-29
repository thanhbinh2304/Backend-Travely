<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class WishlistController extends Controller
{
    /**
     * GET /wishlist - Get user's wishlist
     */
    public function index(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $wishlist = Wishlist::with(['tour.images', 'tour.reviews'])
                ->where('userID', $user->userID)
                ->orderBy('created_at', 'desc')
                ->get();

            $wishlistWithStats = $wishlist->map(function ($item) {
                $tour = $item->tour;
                if ($tour) {
                    $tour->avg_rating = $tour->reviews->avg('rating');
                    $tour->review_count = $tour->reviews->count();
                }
                return $item;
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $wishlist->count(),
                    'items' => $wishlistWithStats
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get wishlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /wishlist - Add tour to wishlist
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tourID' => 'required|integer|exists:tour,tourID'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $user = JWTAuth::parseToken()->authenticate();

            $exists = Wishlist::where('userID', $user->userID)
                ->where('tourID', $request->tourID)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tour already in wishlist'
                ], 400);
            }

            $wishlist = Wishlist::create([
                'userID' => $user->userID,
                'tourID' => $request->tourID,
                'created_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tour added to wishlist',
                'data' => $wishlist
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add tour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /wishlist/{tourID} - Remove tour from wishlist
     */
    public function destroy($tourID)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $wishlist = Wishlist::where('userID', $user->userID)
                ->where('tourID', $tourID)
                ->first();

            if (!$wishlist) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tour not found in wishlist'
                ], 404);
            }

            $wishlist->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tour removed from wishlist'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove tour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /wishlist/toggle/{tourID} - Toggle tour in wishlist
     */
    public function toggle($tourID)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $tour = Tour::find($tourID);
            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tour not found'
                ], 404);
            }

            $wishlist = Wishlist::where('userID', $user->userID)
                ->where('tourID', $tourID)
                ->first();

            if ($wishlist) {
                $wishlist->delete();
                $action = 'removed';
                $inWishlist = false;
            } else {
                Wishlist::create([
                    'userID' => $user->userID,
                    'tourID' => $tourID,
                    'created_at' => now()
                ]);
                $action = 'added';
                $inWishlist = true;
            }

            return response()->json([
                'success' => true,
                'message' => "Tour {$action}",
                'data' => [
                    'in_wishlist' => $inWishlist
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /wishlist/share - Generate shareable link
     */
    public function share()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $tourIDs = Wishlist::where('userID', $user->userID)
                ->pluck('tourID')
                ->toArray();

            if (empty($tourIDs)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wishlist is empty'
                ], 400);
            }

            $token = base64_encode(json_encode([
                'userName' => $user->userName,
                'tourIDs' => $tourIDs,
                'timestamp' => now()->timestamp
            ]));

            return response()->json([
                'success' => true,
                'data' => [
                    'share_url' => url("/api/wishlist/shared/{$token}"),
                    'tours_count' => count($tourIDs)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate link',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /wishlist/shared/{token} - View shared wishlist
     */
    public function viewShared($token)
    {
        try {
            $decoded = json_decode(base64_decode($token), true);

            if (!$decoded || !isset($decoded['tourIDs'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid link'
                ], 400);
            }

            $tours = Tour::with(['images', 'tour.reviews'])
                ->whereIn('tourID', $decoded['tourIDs'])
                ->get();

            $toursWithStats = $tours->map(function ($tour) {
                $tour->avg_rating = $tour->reviews->avg('rating');
                $tour->review_count = $tour->reviews->count();
                return $tour;
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'shared_by' => $decoded['userName'] ?? 'Unknown',
                    'shared_at' => isset($decoded['timestamp']) ? date('Y-m-d H:i:s', $decoded['timestamp']) : null,
                    'tours' => $toursWithStats
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /wishlist/clear - Clear all wishlist
     */
    public function clear()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $count = Wishlist::where('userID', $user->userID)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Wishlist cleared',
                'data' => ['deleted_count' => $count]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
