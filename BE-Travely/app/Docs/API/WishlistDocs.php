<?php

namespace App\Docs\API;

class WishlistDocs
{
    /**
     * @OA\Get(
     *     path="/wishlist",
     *     summary="Get user's wishlist",
     *     description="Get all tours in authenticated user's wishlist",
     *     tags={"Wishlist"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Wishlist retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="wishlistID", type="integer"),
     *                     @OA\Property(property="userID", type="string"),
     *                     @OA\Property(property="tourID", type="integer"),
     *                     @OA\Property(property="addedDate", type="string", format="date-time"),
     *                     @OA\Property(
     *                         property="tour",
     *                         type="object",
     *                         @OA\Property(property="tourID", type="integer"),
     *                         @OA\Property(property="title", type="string"),
     *                         @OA\Property(property="description", type="string"),
     *                         @OA\Property(property="priceAdult", type="number"),
     *                         @OA\Property(property="destination", type="string"),
     *                         @OA\Property(property="images", type="array", @OA\Items(type="object"))
     *                     )
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
     * @OA\Post(
     *     path="/wishlist",
     *     summary="Add tour to wishlist",
     *     description="Add a tour to user's wishlist",
     *     tags={"Wishlist"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"tourID"},
     *             @OA\Property(property="tourID", type="integer", example=1, description="ID of the tour to add to wishlist")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tour added to wishlist successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tour added to wishlist successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="wishlistID", type="integer"),
     *                 @OA\Property(property="tourID", type="integer"),
     *                 @OA\Property(property="addedDate", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Tour already in wishlist or tour not found"
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
     * @OA\Delete(
     *     path="/wishlist/{id}",
     *     summary="Remove tour from wishlist",
     *     description="Remove a tour from user's wishlist",
     *     tags={"Wishlist"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Wishlist item ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tour removed from wishlist successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tour removed from wishlist successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Wishlist item not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Can only remove own wishlist items"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function destroy() {}

    /**
     * @OA\Delete(
     *     path="/wishlist/tour/{tourId}",
     *     summary="Remove tour from wishlist by tour ID",
     *     description="Remove a tour from user's wishlist using tour ID",
     *     tags={"Wishlist"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="tourId",
     *         in="path",
     *         description="Tour ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tour removed from wishlist successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tour removed from wishlist successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tour not found in wishlist"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function removeByTourId() {}

    /**
     * @OA\Get(
     *     path="/wishlist/check/{tourId}",
     *     summary="Check if tour is in wishlist",
     *     description="Check if a specific tour is in user's wishlist",
     *     tags={"Wishlist"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="tourId",
     *         in="path",
     *         description="Tour ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Check completed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="inWishlist", type="boolean", example=true),
     *                 @OA\Property(property="wishlistID", type="integer", example=5)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function checkWishlist() {}

    /**
     * @OA\Delete(
     *     path="/wishlist/clear",
     *     summary="Clear entire wishlist",
     *     description="Remove all tours from user's wishlist",
     *     tags={"Wishlist"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Wishlist cleared successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Wishlist cleared successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function clear() {}
}
