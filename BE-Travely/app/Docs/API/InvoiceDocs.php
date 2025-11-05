<?php

namespace App\Docs\API;

class InvoiceDocs
{
    /**
     * @OA\Get(
     *     path="/invoices",
     *     summary="Get user's invoices",
     *     description="Get all invoices for authenticated user",
     *     tags={"Invoices"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by payment status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "paid", "cancelled", "refunded"})
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Invoices retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="invoiceID", type="integer"),
     *                         @OA\Property(property="bookingID", type="integer"),
     *                         @OA\Property(property="invoiceNumber", type="string", example="INV-2025-0001"),
     *                         @OA\Property(property="totalAmount", type="number", example=599.98),
     *                         @OA\Property(property="discountAmount", type="number", example=60.00),
     *                         @OA\Property(property="finalAmount", type="number", example=539.98),
     *                         @OA\Property(property="paymentStatus", type="string", example="paid"),
     *                         @OA\Property(property="paymentMethod", type="string", example="credit_card"),
     *                         @OA\Property(property="issuedDate", type="string", format="date-time"),
     *                         @OA\Property(property="paidDate", type="string", format="date-time"),
     *                         @OA\Property(property="booking", type="object")
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
     * @OA\Get(
     *     path="/invoices/{id}",
     *     summary="Get invoice details",
     *     description="Get specific invoice information",
     *     tags={"Invoices"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Invoice ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Invoice retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="invoiceID", type="integer"),
     *                 @OA\Property(property="bookingID", type="integer"),
     *                 @OA\Property(property="invoiceNumber", type="string"),
     *                 @OA\Property(property="totalAmount", type="number"),
     *                 @OA\Property(property="discountAmount", type="number"),
     *                 @OA\Property(property="finalAmount", type="number"),
     *                 @OA\Property(property="paymentStatus", type="string"),
     *                 @OA\Property(property="paymentMethod", type="string"),
     *                 @OA\Property(property="issuedDate", type="string", format="date-time"),
     *                 @OA\Property(property="paidDate", type="string", format="date-time"),
     *                 @OA\Property(property="notes", type="string"),
     *                 @OA\Property(
     *                     property="booking",
     *                     type="object",
     *                     @OA\Property(property="bookingID", type="integer"),
     *                     @OA\Property(property="numberOfAdult", type="integer"),
     *                     @OA\Property(property="numberOfChild", type="integer"),
     *                     @OA\Property(property="tour", type="object")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Invoice not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Can only view own invoices"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function show() {}

    /**
     * @OA\Get(
     *     path="/invoices/booking/{bookingId}",
     *     summary="Get invoice by booking ID",
     *     description="Get invoice for a specific booking",
     *     tags={"Invoices"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="bookingId",
     *         in="path",
     *         description="Booking ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Invoice retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Invoice not found for this booking"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function getByBooking() {}

    /**
     * @OA\Get(
     *     path="/invoices/{id}/download",
     *     summary="Download invoice PDF",
     *     description="Download invoice as PDF file",
     *     tags={"Invoices"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Invoice ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Invoice PDF file",
     *         @OA\MediaType(
     *             mediaType="application/pdf"
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Invoice not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Can only download own invoices"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function download() {}

    /**
     * @OA\Post(
     *     path="/invoices/{id}/pay",
     *     summary="Pay an invoice",
     *     description="Process payment for an invoice",
     *     tags={"Invoices"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Invoice ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"paymentMethod"},
     *             @OA\Property(property="paymentMethod", type="string", enum={"credit_card", "debit_card", "paypal", "bank_transfer"}, example="credit_card"),
     *             @OA\Property(property="promotionCode", type="string", example="SUMMER2025")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Payment processed successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="invoiceID", type="integer"),
     *                 @OA\Property(property="paymentStatus", type="string", example="paid"),
     *                 @OA\Property(property="paidDate", type="string", format="date-time"),
     *                 @OA\Property(property="transactionID", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Payment failed or invoice already paid"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Invoice not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function pay() {}

    /**
     * @OA\Get(
     *     path="/admin/invoices",
     *     summary="Get all invoices (Admin)",
     *     description="Get all invoices in the system (Admin only)",
     *     tags={"Invoices"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by payment status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "paid", "cancelled", "refunded"})
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Filter by start date",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Filter by end date",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=20)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="All invoices retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function adminIndex() {}

    /**
     * @OA\Post(
     *     path="/admin/invoices/{id}/refund",
     *     summary="Refund an invoice (Admin)",
     *     description="Process refund for an invoice (Admin only)",
     *     tags={"Invoices"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Invoice ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="reason", type="string", example="Customer requested refund", maxLength=500)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Refund processed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Refund processed successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Cannot refund - invalid status"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Invoice not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function refund() {}
}
