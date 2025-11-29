<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Tour;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

class ReviewController extends Controller
{
    /**
     * GET /reviews - Get all approved reviews (public)
     */
    public function index(Request $request)
    {
        try {
            $query = Review::with(['user', 'tour'])
                ->where('status', Review::STATUS_APPROVED);

            // Filter by tour
            if ($request->filled('tourID')) {
                $query->where('tourID', $request->tourID);
            }

            // Filter by rating
            if ($request->filled('rating')) {
                $query->where('rating', $request->rating);
            }

            // Filter by verified purchase
            if ($request->filled('verified_only') && $request->verified_only) {
                $query->where('is_verified_purchase', true);
            }

            // Sort
            $sortBy = $request->get('sort_by', 'timestamp');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $perPage = $request->get('per_page', 15);
            $reviews = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $reviews
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get reviews',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /reviews/{id} - Get single review
     */
    public function show($id)
    {
        try {
            $review = Review::with(['user', 'tour'])->find($id);

            if (!$review) {
                return response()->json([
                    'success' => false,
                    'message' => 'Review not found'
                ], 404);
            }

            // Only show if approved or if user is the owner
            $user = JWTAuth::parseToken()->authenticate();
            if ($review->status !== Review::STATUS_APPROVED && $review->userID !== $user->userID) {
                return response()->json([
                    'success' => false,
                    'message' => 'Review not available'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $review
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get review',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /user/reviews - Get current user's reviews
     */
    public function myReviews(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $reviews = Review::with(['tour.images'])
                ->where('userID', $user->userID)
                ->orderBy('timestamp', 'desc')
                ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $reviews
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get reviews',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /reviews - Create new review
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tourID' => 'required|integer|exists:tour,tourID',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10',
            'images' => 'nullable|array|max:5',
            'images.*' => 'nullable|string'
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

            // Check if user already reviewed this tour
            $existingReview = Review::where('userID', $user->userID)
                ->where('tourID', $request->tourID)
                ->first();

            if ($existingReview) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already reviewed this tour'
                ], 400);
            }

            // Check if user has booked this tour
            $hasBooking = Booking::where('userID', $user->userID)
                ->where('tourID', $request->tourID)
                ->whereIn('bookingStatus', ['confirmed', 'completed'])
                ->exists();

            $review = Review::create([
                'tourID' => $request->tourID,
                'userID' => $user->userID,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'images' => $request->images ?? [],
                'status' => Review::STATUS_PENDING,
                'is_verified_purchase' => $hasBooking,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Review submitted successfully. Waiting for approval.',
                'data' => $review
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create review',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /reviews/{id} - Update own review
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'sometimes|string|min:10',
            'images' => 'nullable|array|max:5',
            'images.*' => 'nullable|string'
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

            $review = Review::find($id);

            if (!$review) {
                return response()->json([
                    'success' => false,
                    'message' => 'Review not found'
                ], 404);
            }

            // Check ownership
            if ($review->userID !== $user->userID) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to update this review'
                ], 403);
            }

            // Update review and reset to pending
            $review->update([
                'rating' => $request->rating ?? $review->rating,
                'comment' => $request->comment ?? $review->comment,
                'images' => $request->images ?? $review->images,
                'status' => Review::STATUS_PENDING,
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Review updated successfully. Waiting for approval.',
                'data' => $review
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update review',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /reviews/{id} - Delete own review
     */
    public function destroy($id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $review = Review::find($id);

            if (!$review) {
                return response()->json([
                    'success' => false,
                    'message' => 'Review not found'
                ], 404);
            }

            // Check ownership
            if ($review->userID !== $user->userID) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this review'
                ], 403);
            }

            $review->delete();

            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete review',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /reviews/{id}/images - Upload images for review
     */
    public function uploadImages(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'images' => 'required|array|max:5',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120'
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

            $review = Review::find($id);

            if (!$review) {
                return response()->json([
                    'success' => false,
                    'message' => 'Review not found'
                ], 404);
            }

            if ($review->userID !== $user->userID) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $uploadedImages = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('reviews', 'public');
                $uploadedImages[] = Storage::url($path);
            }

            // Merge with existing images
            $existingImages = $review->images ?? [];
            $allImages = array_merge($existingImages, $uploadedImages);

            $review->update([
                'images' => array_slice($allImages, 0, 5), // Max 5 images
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Images uploaded successfully',
                'data' => [
                    'images' => $review->images
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload images',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ==================== ADMIN METHODS ====================

    /**
     * GET /admin/reviews - Get all reviews with filters (Admin)
     */
    public function adminIndex(Request $request)
    {
        try {
            $query = Review::with(['user', 'tour']);

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filter by rating
            if ($request->filled('rating')) {
                $query->where('rating', $request->rating);
            }

            // Filter by tour
            if ($request->filled('tourID')) {
                $query->where('tourID', $request->tourID);
            }

            // Filter by verified purchase
            if ($request->filled('verified_only') && $request->verified_only) {
                $query->where('is_verified_purchase', true);
            }

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('comment', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($q2) use ($search) {
                            $q2->where('userName', 'like', "%{$search}%");
                        });
                });
            }

            $sortBy = $request->get('sort_by', 'timestamp');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $perPage = $request->get('per_page', 20);
            $reviews = $query->paginate($perPage);

            // Add statistics
            $stats = [
                'total' => Review::count(),
                'pending' => Review::where('status', Review::STATUS_PENDING)->count(),
                'approved' => Review::where('status', Review::STATUS_APPROVED)->count(),
                'hidden' => Review::where('status', Review::STATUS_HIDDEN)->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $reviews,
                'statistics' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get reviews',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * PATCH /admin/reviews/{id}/approve - Approve review (Admin)
     */
    public function approve($id)
    {
        try {
            $admin = JWTAuth::parseToken()->authenticate();

            $review = Review::find($id);

            if (!$review) {
                return response()->json([
                    'success' => false,
                    'message' => 'Review not found'
                ], 404);
            }

            $review->update([
                'status' => Review::STATUS_APPROVED,
                'approved_at' => now(),
                'approved_by' => $admin->userID
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Review approved successfully',
                'data' => $review
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve review',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * PATCH /admin/reviews/{id}/hide - Hide review (Admin)
     */
    public function hide($id)
    {
        try {
            $admin = JWTAuth::parseToken()->authenticate();

            $review = Review::find($id);

            if (!$review) {
                return response()->json([
                    'success' => false,
                    'message' => 'Review not found'
                ], 404);
            }

            $review->update([
                'status' => Review::STATUS_HIDDEN,
                'approved_by' => $admin->userID
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Review hidden successfully',
                'data' => $review
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to hide review',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /admin/reviews/{id} - Delete review (Admin)
     */
    public function adminDestroy($id)
    {
        try {
            $review = Review::find($id);

            if (!$review) {
                return response()->json([
                    'success' => false,
                    'message' => 'Review not found'
                ], 404);
            }

            $review->delete();

            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete review',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
