<?php

namespace App\Docs\API;

class TourDocs
{
    /**
     * @OA\Get(
     *     path="/tours",
     *     summary="Get all tours with filters",
     *     description="Get paginated list of tours with optional filters",
     *     tags={"Tours"},
     *     @OA\Parameter(
     *         name="destination",
     *         in="query",
     *         description="Filter by destination",
     *         required=false,
     *         @OA\Schema(type="string", example="Paris")
     *     ),
     *     @OA\Parameter(
     *         name="availability",
     *         in="query",
     *         description="Filter by availability (0 or 1)",
     *         required=false,
     *         @OA\Schema(type="integer", enum={0, 1}, example=1)
     *     ),
     *     @OA\Parameter(
     *         name="min_price",
     *         in="query",
     *         description="Minimum price filter",
     *         required=false,
     *         @OA\Schema(type="number", example=100)
     *     ),
     *     @OA\Parameter(
     *         name="max_price",
     *         in="query",
     *         description="Maximum price filter",
     *         required=false,
     *         @OA\Schema(type="number", example=1000)
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Filter tours starting from this date",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Filter tours ending before this date",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-12-31")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sort field",
     *         required=false,
     *         @OA\Schema(type="string", example="tourID")
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
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tours retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="tourID", type="integer", example=1),
     *                         @OA\Property(property="title", type="string", example="Paris City Tour"),
     *                         @OA\Property(property="description", type="string", example="Amazing tour of Paris"),
     *                         @OA\Property(property="quantity", type="integer", example=20),
     *                         @OA\Property(property="priceAdult", type="number", example=299.99),
     *                         @OA\Property(property="priceChild", type="number", example=149.99),
     *                         @OA\Property(property="destination", type="string", example="Paris, France"),
     *                         @OA\Property(property="availability", type="integer", example=1),
     *                         @OA\Property(property="startDate", type="string", format="date", example="2025-06-01"),
     *                         @OA\Property(property="endDate", type="string", format="date", example="2025-06-07")
     *                     )
     *                 ),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=100)
     *             )
     *         )
     *     )
     * )
     */
    public function index() {}

    /**
     * @OA\Get(
     *     path="/tours/{id}",
     *     summary="Get tour by ID",
     *     description="Get detailed information about a specific tour",
     *     tags={"Tours"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Tour ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tour retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="tourID", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Paris City Tour"),
     *                 @OA\Property(property="description", type="string", example="Amazing tour of Paris"),
     *                 @OA\Property(property="quantity", type="integer", example=20),
     *                 @OA\Property(property="priceAdult", type="number", example=299.99),
     *                 @OA\Property(property="priceChild", type="number", example=149.99),
     *                 @OA\Property(property="destination", type="string", example="Paris, France"),
     *                 @OA\Property(property="availability", type="integer", example=1),
     *                 @OA\Property(property="startDate", type="string", format="date"),
     *                 @OA\Property(property="endDate", type="string", format="date"),
     *                 @OA\Property(
     *                     property="images",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="imageID", type="integer"),
     *                         @OA\Property(property="imageURL", type="string")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="itineraries",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="itineraryID", type="integer"),
     *                         @OA\Property(property="dayNumber", type="integer"),
     *                         @OA\Property(property="activity", type="string")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="reviews",
     *                     type="array",
     *                     @OA\Items(type="object")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tour not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Tour not found")
     *         )
     *     )
     * )
     */
    public function show() {}

    /**
     * @OA\Post(
     *     path="/tours",
     *     summary="Create a new tour",
     *     description="Create a new tour (Admin only)",
     *     tags={"Tours"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","description","quantity","priceAdult","priceChild","destination","startDate","endDate"},
     *             @OA\Property(property="title", type="string", example="Paris City Tour", maxLength=255),
     *             @OA\Property(property="description", type="string", example="Experience the beauty of Paris with our guided tour"),
     *             @OA\Property(property="quantity", type="integer", example=20, minimum=1),
     *             @OA\Property(property="priceAdult", type="number", example=299.99, minimum=0),
     *             @OA\Property(property="priceChild", type="number", example=149.99, minimum=0),
     *             @OA\Property(property="destination", type="string", example="Paris, France", maxLength=255),
     *             @OA\Property(property="availability", type="boolean", example=true),
     *             @OA\Property(property="startDate", type="string", format="date", example="2025-06-01"),
     *             @OA\Property(property="endDate", type="string", format="date", example="2025-06-07"),
     *             @OA\Property(
     *                 property="images",
     *                 type="array",
     *                 @OA\Items(type="string", format="url", example="https://example.com/image.jpg")
     *             ),
     *             @OA\Property(
     *                 property="itineraries",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="dayNumber", type="integer", example=1),
     *                     @OA\Property(property="activity", type="string", example="Visit Eiffel Tower")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tour created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tour created successfully"),
     *             @OA\Property(property="data", type="object")
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
    public function store() {}

    /**
     * @OA\Put(
     *     path="/tours/{id}",
     *     summary="Update a tour",
     *     description="Update an existing tour (Admin only)",
     *     tags={"Tours"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Tour ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated Paris Tour", maxLength=255),
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="quantity", type="integer", example=25, minimum=1),
     *             @OA\Property(property="priceAdult", type="number", example=349.99, minimum=0),
     *             @OA\Property(property="priceChild", type="number", example=174.99, minimum=0),
     *             @OA\Property(property="destination", type="string", example="Paris, France", maxLength=255),
     *             @OA\Property(property="availability", type="boolean", example=true),
     *             @OA\Property(property="startDate", type="string", format="date", example="2025-07-01"),
     *             @OA\Property(property="endDate", type="string", format="date", example="2025-07-07"),
     *             @OA\Property(
     *                 property="images",
     *                 type="array",
     *                 @OA\Items(type="string", format="url")
     *             ),
     *             @OA\Property(
     *                 property="itineraries",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="dayNumber", type="integer"),
     *                     @OA\Property(property="activity", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tour updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tour updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tour not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function update() {}

    /**
     * @OA\Delete(
     *     path="/tours/{id}",
     *     summary="Delete a tour",
     *     description="Delete an existing tour (Admin only)",
     *     tags={"Tours"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Tour ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tour deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tour deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tour not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function destroy() {}

    /**
     * @OA\Get(
     *     path="/tours/featured",
     *     summary="Get featured tours",
     *     description="Get list of featured/popular tours",
     *     tags={"Tours"},
     *     @OA\Response(
     *         response=200,
     *         description="Featured tours retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function featured() {}

    /**
     * @OA\Get(
     *     path="/tours/search",
     *     summary="Search tours",
     *     description="Search tours by keyword",
     *     tags={"Tours"},
     *     @OA\Parameter(
     *         name="keyword",
     *         in="query",
     *         description="Search keyword (minimum 2 characters)",
     *         required=true,
     *         @OA\Schema(type="string", example="Paris", minLength=2)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Search results retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function search() {}

    /**
     * @OA\Get(
     *     path="/tours/available",
     *     summary="Get available tours",
     *     description="Get tours that are currently available for booking",
     *     tags={"Tours"},
     *     @OA\Response(
     *         response=200,
     *         description="Available tours retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function available() {}

    /**
     * @OA\Get(
     *     path="/tours/destination/{destination}",
     *     summary="Get tours by destination",
     *     description="Get all tours for a specific destination",
     *     tags={"Tours"},
     *     @OA\Parameter(
     *         name="destination",
     *         in="path",
     *         description="Destination name",
     *         required=true,
     *         @OA\Schema(type="string", example="Paris")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tours retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function byDestination() {}

    /**
     * @OA\Patch(
     *     path="/tours/{id}/availability",
     *     summary="Update tour availability",
     *     description="Update availability status of a tour (Admin only)",
     *     tags={"Tours"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Tour ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"availability"},
     *             @OA\Property(property="availability", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tour availability updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tour availability updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tour not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function updateAvailability() {}

    /**
     * @OA\Patch(
     *     path="/tours/{id}/quantity",
     *     summary="Update tour quantity",
     *     description="Update available quantity of a tour (Admin only)",
     *     tags={"Tours"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Tour ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"quantity"},
     *             @OA\Property(property="quantity", type="integer", example=50, minimum=0)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tour quantity updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tour quantity updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tour not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function updateQuantity() {}
}
