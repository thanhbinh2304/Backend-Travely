<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Promotions",
 *     description="API Endpoints for managing promotions"
 * )
 */
class PromotionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/promotions",
     *     summary="Get all promotions",
     *     tags={"Promotions"},
     *     @OA\Parameter(
     *         name="active",
     *         in="query",
     *         description="Filter active promotions only",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Promotions retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Promotion"))
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Promotion::query();

            // Filter active promotions if requested
            if ($request->has('active') && $request->active) {
                $now = now();
                $query->where('startDate', '<=', $now)
                    ->where('endDate', '>=', $now)
                    ->where('quantity', '>', 0);
            }

            $promotions = $query->orderBy('startDate', 'desc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Promotions retrieved successfully',
                'data' => $promotions
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve promotions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/promotions",
     *     summary="Create a new promotion",
     *     tags={"Promotions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"description","discount","startDate","endDate","quantity"},
     *             @OA\Property(property="description", type="string", example="Summer Sale 2025"),
     *             @OA\Property(property="discount", type="number", format="decimal", example=20.00),
     *             @OA\Property(property="startDate", type="string", format="date", example="2025-06-01"),
     *             @OA\Property(property="endDate", type="string", format="date", example="2025-08-31"),
     *             @OA\Property(property="quantity", type="integer", example=100)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Promotion created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Promotion created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Promotion")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'description' => 'required|string|max:255',
                'discount' => 'required|numeric|min:0|max:100',
                'startDate' => 'required|date',
                'endDate' => 'required|date|after_or_equal:startDate',
                'quantity' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $promotion = Promotion::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Promotion created successfully',
                'data' => $promotion
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create promotion',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/promotions/{id}",
     *     summary="Get a specific promotion",
     *     tags={"Promotions"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Promotion ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Promotion retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Promotion")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Promotion not found"
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        try {
            $promotion = Promotion::find($id);

            if (!$promotion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Promotion not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Promotion retrieved successfully',
                'data' => $promotion
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve promotion',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/promotions/{id}",
     *     summary="Update a promotion",
     *     tags={"Promotions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Promotion ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="description", type="string", example="Updated Summer Sale 2025"),
     *             @OA\Property(property="discount", type="number", format="decimal", example=25.00),
     *             @OA\Property(property="startDate", type="string", format="date", example="2025-06-01"),
     *             @OA\Property(property="endDate", type="string", format="date", example="2025-09-30"),
     *             @OA\Property(property="quantity", type="integer", example=150)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Promotion updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Promotion updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Promotion")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Promotion not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $promotion = Promotion::find($id);

            if (!$promotion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Promotion not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'description' => 'sometimes|required|string|max:255',
                'discount' => 'sometimes|required|numeric|min:0|max:100',
                'startDate' => 'sometimes|required|date',
                'endDate' => 'sometimes|required|date|after_or_equal:startDate',
                'quantity' => 'sometimes|required|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $promotion->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Promotion updated successfully',
                'data' => $promotion->fresh()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update promotion',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/promotions/{id}",
     *     summary="Delete a promotion",
     *     tags={"Promotions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Promotion ID",
     *         required=true,
     *         @OA\Schema(type="integer")
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
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        try {
            $promotion = Promotion::find($id);

            if (!$promotion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Promotion not found'
                ], 404);
            }

            $promotion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Promotion deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete promotion',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
