<?php

namespace App\Docs\API;

class PromotionDocs
{
    /**
     * @OA\Get(
     *     path="/promotions",
     *     summary="Get all promotions",
     *     description="Get list of all active promotions",
     *     tags={"Promotions"},
     *     @OA\Parameter(
     *         name="active",
     *         in="query",
     *         description="Filter by active status",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Promotions retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="promotionID", type="integer"),
     *                     @OA\Property(property="code", type="string", example="SUMMER2025"),
     *                     @OA\Property(property="description", type="string", example="Summer discount 20%"),
     *                     @OA\Property(property="discountPercentage", type="number", example=20.00),
     *                     @OA\Property(property="startDate", type="string", format="date"),
     *                     @OA\Property(property="endDate", type="string", format="date"),
     *                     @OA\Property(property="isActive", type="boolean", example=true)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index() {}

    /**
     * @OA\Get(
     *     path="/promotions/{id}",
     *     summary="Get promotion by ID",
     *     description="Get specific promotion details",
     *     tags={"Promotions"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Promotion ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Promotion retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="promotionID", type="integer"),
     *                 @OA\Property(property="code", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="discountPercentage", type="number"),
     *                 @OA\Property(property="startDate", type="string", format="date"),
     *                 @OA\Property(property="endDate", type="string", format="date"),
     *                 @OA\Property(property="isActive", type="boolean")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Promotion not found"
     *     )
     * )
     */
    public function show() {}

    /**
     * @OA\Post(
     *     path="/promotions/validate",
     *     summary="Validate promotion code",
     *     description="Check if a promotion code is valid and applicable",
     *     tags={"Promotions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code"},
     *             @OA\Property(property="code", type="string", example="SUMMER2025", description="Promotion code to validate")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Promotion code is valid",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Promotion code is valid"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="promotionID", type="integer"),
     *                 @OA\Property(property="code", type="string"),
     *                 @OA\Property(property="discountPercentage", type="number", example=20.00),
     *                 @OA\Property(property="description", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid or expired promotion code",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Promotion code is invalid or expired")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function validateCode() {}

    /**
     * @OA\Post(
     *     path="/promotions",
     *     summary="Create a promotion",
     *     description="Create a new promotion (Admin only)",
     *     tags={"Promotions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code","description","discountPercentage","startDate","endDate"},
     *             @OA\Property(property="code", type="string", example="WINTER2025", maxLength=50),
     *             @OA\Property(property="description", type="string", example="Winter special discount 30%", maxLength=255),
     *             @OA\Property(property="discountPercentage", type="number", example=30.00, minimum=0, maximum=100),
     *             @OA\Property(property="startDate", type="string", format="date", example="2025-12-01"),
     *             @OA\Property(property="endDate", type="string", format="date", example="2025-12-31"),
     *             @OA\Property(property="isActive", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Promotion created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Promotion created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required"
     *     )
     * )
     */
    public function store() {}

    /**
     * @OA\Put(
     *     path="/promotions/{id}",
     *     summary="Update a promotion",
     *     description="Update an existing promotion (Admin only)",
     *     tags={"Promotions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Promotion ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="string", maxLength=50),
     *             @OA\Property(property="description", type="string", maxLength=255),
     *             @OA\Property(property="discountPercentage", type="number", minimum=0, maximum=100),
     *             @OA\Property(property="startDate", type="string", format="date"),
     *             @OA\Property(property="endDate", type="string", format="date"),
     *             @OA\Property(property="isActive", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Promotion updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Promotion updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Promotion not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required"
     *     )
     * )
     */
    public function update() {}

    /**
     * @OA\Delete(
     *     path="/promotions/{id}",
     *     summary="Delete a promotion",
     *     description="Delete an existing promotion (Admin only)",
     *     tags={"Promotions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Promotion ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Promotion deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Promotion deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Promotion not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required"
     *     )
     * )
     */
    public function destroy() {}

    /**
     * @OA\Patch(
     *     path="/promotions/{id}/toggle",
     *     summary="Toggle promotion status",
     *     description="Activate or deactivate a promotion (Admin only)",
     *     tags={"Promotions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Promotion ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Promotion status toggled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Promotion status updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Promotion not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required"
     *     )
     * )
     */
    public function toggleStatus() {}
}
