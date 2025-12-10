<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Models\TourImage;
use App\Models\TourItinerary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Booking;
use App\Models\Review;

class TourController extends Controller
{
    /**
     * Upload tour image
     * Admin only
     */
    public function uploadImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // Max 5MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $image = $request->file('image');
            $fileName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

            // Store in public/storage/tours
            $path = $image->storeAs('tours', $fileName, 'public');

            $imageUrl = url('storage/' . $path);
            // Force HTTPS
            $imageUrl = str_replace('http://', 'https://', $imageUrl);

            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'data' => [
                    'url' => $imageUrl,
                    'path' => $path
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of tours (with pagination and filters)
     * Public access
     */
    public function index(Request $request)
    {
        $query = Tour::with(['images', 'itineraries']);

        // Filter by destination
        if ($request->has('destination')) {
            $query->where('destination', 'like', '%' . $request->destination . '%');
        }

        // Filter by availability
        if ($request->has('availability')) {
            $query->where('availability', $request->availability);
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('priceAdult', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('priceAdult', '<=', $request->max_price);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('startDate', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('endDate', '<=', $request->end_date);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'tourID');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $tours = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $tours
        ]);
    }

    /**
     * Store a newly created tour
     * Admin only
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'priceAdult' => 'required|numeric|min:0',
            'priceChild' => 'required|numeric|min:0',
            'destination' => 'required|string|max:255',
            'availability' => 'nullable|boolean',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'images' => 'nullable|array',
            'images.*' => 'nullable|string', // Accept URL string (from upload API)
            'image_files' => 'nullable|array',
            'image_files.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'itineraries' => 'nullable|array',
            'itineraries.*.dayNumber' => 'required|integer|min:1',
            'itineraries.*.destination' => 'required|string',
            'itineraries.*.activity' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Create tour
            $tour = Tour::create([
                'title' => $request->title,
                'description' => $request->description,
                'quantity' => $request->quantity,
                'priceAdult' => $request->priceAdult,
                'priceChild' => $request->priceChild,
                'destination' => $request->destination,
                'availability' => $request->availability ?? 1,
                'startDate' => $request->startDate,
                'endDate' => $request->endDate,
            ]);

            // Handle image files upload
            if ($request->hasFile('image_files')) {
                foreach ($request->file('image_files') as $image) {
                    $fileName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $path = $image->storeAs('tours', $fileName, 'public');
                    $imageUrl = url('storage/' . $path);
                    $imageUrl = str_replace('http://', 'https://', $imageUrl);

                    TourImage::create([
                        'tourID' => $tour->tourID,
                        'imageURL' => $imageUrl,
                    ]);
                }
            }

            // Add images from URLs if provided
            if ($request->has('images') && is_array($request->images)) {
                foreach ($request->images as $imageUrl) {
                    TourImage::create([
                        'tourID' => $tour->tourID,
                        'imageURL' => $imageUrl,
                    ]);
                }
            }

            // Add itineraries if provided
            if ($request->has('itineraries') && is_array($request->itineraries)) {
                foreach ($request->itineraries as $itinerary) {
                    TourItinerary::create([
                        'tourID' => $tour->tourID,
                        'dayNumber' => $itinerary['dayNumber'],
                        'destination' => $itinerary['destination'],
                        'activity' => $itinerary['activity'],
                    ]);
                }
            }

            DB::commit();

            // Load relationships
            $tour->load(['images', 'itineraries']);

            return response()->json([
                'success' => true,
                'message' => 'Tour created successfully',
                'data' => $tour
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create tour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified tour
     * Public access
     */
    public function show($id)
    {
        $tour = Tour::with(['images', 'itineraries', 'reviews.user'])->find($id);

        if (!$tour) {
            return response()->json([
                'success' => false,
                'message' => 'Tour not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $tour
        ]);
    }

    /**
     * Update the specified tour
     * Admin only
     */
    public function update(Request $request, $id)
    {
        $tour = Tour::find($id);

        if (!$tour) {
            return response()->json([
                'success' => false,
                'message' => 'Tour not found'
            ], 404);
        }

        // Log request data for debugging
        Log::info('Tour Update Request Data:', $request->all());

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'quantity' => 'sometimes|integer|min:1',
            'priceAdult' => 'sometimes|numeric|min:0',
            'priceChild' => 'sometimes|numeric|min:0',
            'destination' => 'sometimes|string|max:255',
            'availability' => 'sometimes|boolean',
            'startDate' => 'sometimes|date',
            'endDate' => 'sometimes|date|after_or_equal:startDate',
            'images' => 'nullable|array',
            'images.*' => 'nullable|string',
            'image_files' => 'nullable|array',
            'image_files.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'itineraries' => 'nullable|array',
            'itineraries.*.dayNumber' => 'required|integer|min:1',
            'itineraries.*.destination' => 'required|string',
            'itineraries.*.activity' => 'required|string',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed:', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Update tour
            $tour->update($request->only([
                'title',
                'description',
                'quantity',
                'priceAdult',
                'priceChild',
                'destination',
                'availability',
                'startDate',
                'endDate'
            ]));

            // Update images if provided
            if ($request->has('images') || $request->hasFile('image_files')) {
                // Delete old images
                TourImage::where('tourID', $tour->tourID)->delete();

                // Handle new image files upload
                if ($request->hasFile('image_files')) {
                    foreach ($request->file('image_files') as $image) {
                        $fileName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                        $path = $image->storeAs('tours', $fileName, 'public');
                        $imageUrl = url('storage/' . $path);
                        $imageUrl = str_replace('http://', 'https://', $imageUrl);

                        TourImage::create([
                            'tourID' => $tour->tourID,
                            'imageURL' => $imageUrl,
                        ]);
                    }
                }

                // Add images from URLs
                if ($request->has('images') && is_array($request->images)) {
                    foreach ($request->images as $imageUrl) {
                        TourImage::create([
                            'tourID' => $tour->tourID,
                            'imageURL' => $imageUrl,
                        ]);
                    }
                }
            }

            // Update itineraries if provided
            if ($request->has('itineraries')) {
                // Delete old itineraries
                TourItinerary::where('tourID', $tour->tourID)->delete();

                // Add new itineraries
                if (is_array($request->itineraries)) {
                    foreach ($request->itineraries as $itinerary) {
                        TourItinerary::create([
                            'tourID' => $tour->tourID,
                            'dayNumber' => $itinerary['dayNumber'],
                            'destination' => $itinerary['destination'],
                            'activity' => $itinerary['activity'],
                        ]);
                    }
                }
            }

            DB::commit();

            // Load relationships
            $tour->load(['images', 'itineraries']);

            return response()->json([
                'success' => true,
                'message' => 'Tour updated successfully',
                'data' => $tour
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified tour
     * Admin only
     */
    public function destroy($id)
    {
        $tour = Tour::find($id);

        if (!$tour) {
            return response()->json([
                'success' => false,
                'message' => 'Tour not found'
            ], 404);
        }

        DB::beginTransaction();
        try {
            // Delete related images
            TourImage::where('tourID', $tour->tourID)->delete();

            // Delete related itineraries
            TourItinerary::where('tourID', $tour->tourID)->delete();

            // Delete tour
            $tour->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tour deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete tour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get featured/popular tours
     * Public access
     */
    public function featured()
    {
        $tours = Tour::with(['images'])
            ->where('availability', 1)
            ->orderBy('tourID', 'desc')
            ->limit(6)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tours
        ]);
    }

    /**
     * Search tours
     * Public access
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'keyword' => 'required|string|min:2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $keyword = $request->keyword;

        $tours = Tour::with(['images'])
            ->where(function ($query) use ($keyword) {
                $query->where('title', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%")
                    ->orWhere('destination', 'like', "%{$keyword}%");
            })
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $tours
        ]);
    }

    /**
     * Get available tours (quantity > 0 and availability = 1)
     * Public access
     */
    public function available()
    {
        $tours = Tour::with(['images'])
            ->where('availability', 1)
            ->where('quantity', '>', 0)
            ->where('startDate', '>=', now())
            ->orderBy('startDate', 'asc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $tours
        ]);
    }

    /**
     * Get tours by destination
     * Public access
     */
    public function byDestination($destination)
    {
        $tours = Tour::with(['images'])
            ->where('destination', 'like', "%{$destination}%")
            ->where('availability', 1)
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $tours
        ]);
    }

    /**
     * Update tour availability
     * Admin only
     */
    public function updateAvailability(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'availability' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $tour = Tour::find($id);

        if (!$tour) {
            return response()->json([
                'success' => false,
                'message' => 'Tour not found'
            ], 404);
        }

        $tour->availability = $request->availability;
        $tour->save();

        return response()->json([
            'success' => true,
            'message' => 'Tour availability updated successfully',
            'data' => $tour
        ]);
    }

    /**
     * Update tour quantity
     * Admin only
     */
    public function updateQuantity(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $tour = Tour::find($id);

        if (!$tour) {
            return response()->json([
                'success' => false,
                'message' => 'Tour not found'
            ], 404);
        }

        $tour->quantity = $request->quantity;
        $tour->save();

        return response()->json([
            'success' => true,
            'message' => 'Tour quantity updated successfully',
            'data' => $tour
        ]);
    }
}
