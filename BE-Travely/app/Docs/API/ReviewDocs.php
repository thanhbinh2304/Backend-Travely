<?php

namespace App\Docs\API;

class ReviewDocs
{
    /**
     * @OA\Get(
     *     path="/tours/{tourId}/reviews",
     *     summary="Get tour reviews",
     *     description="Get all reviews for a specific tour",
     *     tags={"Reviews"},
     *     @OA\Parameter(
     *         name="tourId",
     *         in="path",
     *         description="Tour ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reviews retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="reviewID", type="integer"),
     *                     @OA\Property(property="userID", type="string"),
     *                     @OA\Property(property="tourID", type="integer"),
     *                     @OA\Property(property="rating", type="integer", example=5),
     *                     @OA\Property(property="comment", type="string"),
     *                     @OA\Property(property="reviewDate", type="string", format="date-time"),
     *                     @OA\Property(
     *                         property="user",
     *                         type="object",
     *                         @OA\Property(property="userName", type="string"),
     *                         @OA\Property(property="email", type="string")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tour not found"
     *     )
     * )
     */
    public function index() {}

    /**
     * @OA\Post(
     *     path="/tours/{tourId}/reviews",
     *     summary="Create a review",
     *     description="Create a review for a tour (must have completed booking)",
     *     tags={"Reviews"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="tourId",
     *         in="path",
     *         description="Tour ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"rating","comment"},
     *             @OA\Property(property="rating", type="integer", example=5, minimum=1, maximum=5, description="Rating from 1 to 5 stars"),
     *             @OA\Property(property="comment", type="string", example="Amazing tour! Highly recommended.", maxLength=1000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Review created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Review created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="reviewID", type="integer"),
     *                 @OA\Property(property="rating", type="integer"),
     *                 @OA\Property(property="comment", type="string"),
     *                 @OA\Property(property="reviewDate", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Cannot review - no completed booking found"
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
     * @OA\Get(
     *     path="/reviews/{id}",
     *     summary="Get review details",
     *     description="Get specific review information",
     *     tags={"Reviews"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Review ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="reviewID", type="integer"),
     *                 @OA\Property(property="userID", type="string"),
     *                 @OA\Property(property="tourID", type="integer"),
     *                 @OA\Property(property="rating", type="integer"),
     *                 @OA\Property(property="comment", type="string"),
     *                 @OA\Property(property="reviewDate", type="string", format="date-time"),
     *                 @OA\Property(property="user", type="object"),
     *                 @OA\Property(property="tour", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Review not found"
     *     )
     * )
     */
    public function show() {}

    /**
     * @OA\Put(
     *     path="/reviews/{id}",
     *     summary="Update a review",
     *     description="Update user's own review",
     *     tags={"Reviews"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Review ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="rating", type="integer", example=4, minimum=1, maximum=5),
     *             @OA\Property(property="comment", type="string", example="Updated review comment", maxLength=1000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Review updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Can only update own reviews"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Review not found"
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
     *     path="/reviews/{id}",
     *     summary="Delete a review",
     *     description="Delete user's own review or any review (Admin)",
     *     tags={"Reviews"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Review ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Review deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Can only delete own reviews"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Review not found"
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
     *     path="/user/reviews",
     *     summary="Get user's reviews",
     *     description="Get all reviews created by authenticated user",
     *     tags={"Reviews"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User reviews retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="reviewID", type="integer"),
     *                     @OA\Property(property="tourID", type="integer"),
     *                     @OA\Property(property="rating", type="integer"),
     *                     @OA\Property(property="comment", type="string"),
     *                     @OA\Property(property="reviewDate", type="string", format="date-time"),
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
    public function userReviews() {}
}
