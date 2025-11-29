<?php

namespace App\Http\Controllers;

use App\Models\Checkout;
use App\Models\Booking;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/payment/momo/create",
     *     summary="Create MoMo payment",
     *     tags={"Payment"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"bookingID", "amount"},
     *             @OA\Property(property="bookingID", type="integer", example=1),
     *             @OA\Property(property="amount", type="number", format="float", example=1000000),
     *             @OA\Property(property="orderInfo", type="string", example="Payment for tour booking")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Payment URL created successfully"),
     *     @OA\Response(response=400, description="Invalid request"),
     *     @OA\Response(response=404, description="Booking not found")
     * )
     */
    public function createMoMoPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bookingID' => 'required|integer|exists:booking,bookingID',
            'amount' => 'required|numeric|min:1000',
            'orderInfo' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $booking = Booking::find($request->bookingID);

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found'
                ], 404);
            }

            // Create order ID
            $orderId = 'BOOKING_' . $request->bookingID . '_' . time();

            // MoMo Config
            $partnerCode = config('services.momo.partner_code');
            $accessKey = config('services.momo.access_key');
            $secretKey = config('services.momo.secret_key');
            $endpoint = config('services.momo.endpoint');
            $returnUrl = config('services.momo.return_url');
            $notifyUrl = config('services.momo.notify_url');

            $requestId = time() . '';
            $orderInfo = $request->orderInfo ?? 'Payment for booking #' . $request->bookingID;
            $redirectUrl = $returnUrl;
            $ipnUrl = $notifyUrl;
            $extraData = base64_encode(json_encode(['bookingID' => $request->bookingID]));
            $requestType = 'captureWallet';

            // Create signature
            $rawHash = "accessKey=" . $accessKey .
                "&amount=" . $request->amount .
                "&extraData=" . $extraData .
                "&ipnUrl=" . $ipnUrl .
                "&orderId=" . $orderId .
                "&orderInfo=" . $orderInfo .
                "&partnerCode=" . $partnerCode .
                "&redirectUrl=" . $redirectUrl .
                "&requestId=" . $requestId .
                "&requestType=" . $requestType;

            $signature = hash_hmac('sha256', $rawHash, $secretKey);

            // Send request to MoMo
            $response = Http::post($endpoint, [
                'partnerCode' => $partnerCode,
                'accessKey' => $accessKey,
                'requestId' => $requestId,
                'amount' => (string)$request->amount,
                'orderId' => $orderId,
                'orderInfo' => $orderInfo,
                'redirectUrl' => $redirectUrl,
                'ipnUrl' => $ipnUrl,
                'extraData' => $extraData,
                'requestType' => $requestType,
                'signature' => $signature,
                'lang' => 'vi'
            ]);

            $result = $response->json();

            if (isset($result['resultCode']) && $result['resultCode'] == 0) {
                // Save checkout record
                $checkout = Checkout::create([
                    'bookingID' => $request->bookingID,
                    'paymentMethod' => 'momo',
                    'amount' => $request->amount,
                    'paymentStatus' => 'pending',
                    'transactionID' => $orderId,
                    'paymentData' => json_encode([
                        'requestId' => $requestId,
                        'orderId' => $orderId
                    ])
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment URL created successfully',
                    'data' => [
                        'checkoutID' => $checkout->checkoutID,
                        'payUrl' => $result['payUrl'],
                        'qrCodeUrl' => $result['qrCodeUrl'] ?? null,
                        'deeplink' => $result['deeplink'] ?? null,
                        'orderId' => $orderId
                    ]
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Payment creation failed'
            ], 400);
        } catch (\Exception $e) {
            Log::error('Create MoMo Payment Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Payment creation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/payment/momo/callback",
     *     summary="MoMo payment callback",
     *     tags={"Payment"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(response=200, description="Callback processed")
     * )
     */
    public function momoCallback(Request $request)
    {
        Log::info('MoMo Callback Received', $request->all());

        try {
            // Verify signature
            $secretKey = config('services.momo.secret_key');
            $accessKey = config('services.momo.access_key');

            $rawHash = "accessKey=" . $accessKey .
                "&amount=" . $request->amount .
                "&extraData=" . $request->extraData .
                "&message=" . $request->message .
                "&orderId=" . $request->orderId .
                "&orderInfo=" . $request->orderInfo .
                "&orderType=" . $request->orderType .
                "&partnerCode=" . $request->partnerCode .
                "&payType=" . $request->payType .
                "&requestId=" . $request->requestId .
                "&responseTime=" . $request->responseTime .
                "&resultCode=" . $request->resultCode .
                "&transId=" . $request->transId;

            $signature = hash_hmac('sha256', $rawHash, $secretKey);

            if ($signature != $request->signature) {
                Log::error('MoMo Invalid Signature');
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid signature'
                ], 400);
            }

            $orderId = $request->orderId;
            $resultCode = $request->resultCode;
            $transId = $request->transId;

            // Find checkout record
            $checkout = Checkout::where('transactionID', $orderId)->first();

            if (!$checkout) {
                Log::error('Checkout not found for orderId: ' . $orderId);
                return response()->json([
                    'success' => false,
                    'message' => 'Checkout not found'
                ], 404);
            }

            // Update payment status
            if ($resultCode == 0) {
                $checkout->update([
                    'paymentStatus' => 'completed',
                    'paymentDate' => now(),
                    'transactionID' => $transId,
                    'paymentData' => json_encode($request->all())
                ]);

                // Update booking status
                $booking = $checkout->booking;
                if ($booking && $booking->bookingStatus == 'pending') {
                    $booking->update(['bookingStatus' => 'confirmed']);
                }

                // Auto create invoice
                if ($booking && !$booking->invoice) {
                    Invoice::create([
                        'bookingID' => $booking->bookingID,
                        'amount' => $checkout->amount,
                        'dateIssued' => now(),
                        'details' => json_encode([
                            'payment_method' => 'MoMo',
                            'transaction_id' => $transId,
                            'order_id' => $orderId,
                            'payment_date' => now()->format('Y-m-d H:i:s')
                        ])
                    ]);
                    Log::info('Invoice created for bookingID: ' . $booking->bookingID);
                }

                Log::info('Payment Success for orderId: ' . $orderId);
            } else {
                $checkout->update([
                    'paymentStatus' => 'failed',
                    'paymentData' => json_encode($request->all())
                ]);

                Log::info('Payment Failed for orderId: ' . $orderId);
            }

            return response()->json([
                'success' => true,
                'message' => 'Callback processed'
            ], 200);
        } catch (\Exception $e) {
            Log::error('MoMo Callback Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/payment/momo/return",
     *     summary="MoMo payment return URL",
     *     tags={"Payment"},
     *     @OA\Response(response=200, description="Return page")
     * )
     */
    public function momoReturn(Request $request)
    {
        $orderId = $request->orderId;
        $resultCode = $request->resultCode;

        $checkout = Checkout::where('transactionID', $orderId)->first();

        if (!$checkout) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => $resultCode == 0 ? 'Payment successful' : 'Payment failed',
            'data' => [
                'checkoutID' => $checkout->checkoutID,
                'bookingID' => $checkout->bookingID,
                'paymentStatus' => $checkout->paymentStatus,
                'amount' => $checkout->amount,
                'resultCode' => $resultCode
            ]
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/payment/vietqr/create",
     *     summary="Create VietQR payment",
     *     tags={"Payment"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"bookingID", "amount"},
     *             @OA\Property(property="bookingID", type="integer", example=1),
     *             @OA\Property(property="amount", type="number", format="float", example=1000000),
     *             @OA\Property(property="description", type="string", example="Payment for tour booking")
     *         )
     *     ),
     *     @OA\Response(response=200, description="QR code generated successfully"),
     *     @OA\Response(response=400, description="Invalid request")
     * )
     */
    public function createVietQRPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bookingID' => 'required|integer|exists:booking,bookingID',
            'amount' => 'required|numeric|min:1000',
            'description' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $booking = Booking::find($request->bookingID);

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found'
                ], 404);
            }

            // Create order ID
            $orderId = 'BOOKING_' . $request->bookingID . '_' . time();
            $description = $request->description ?? 'Booking ' . $request->bookingID;

            // VietQR Config
            $bankId = config('services.vietqr.bank_id', '970415'); // VietinBank default
            $accountNo = config('services.vietqr.account_no');
            $accountName = config('services.vietqr.account_name');
            $template = config('services.vietqr.template', 'compact');

            // Generate QR using VietQR API
            $qrContent = "00020101021238{$bankId}0010A000000727012{accountNo}0208QRIBFTTA5303704{$request->amount}5802VN62{description}6304";

            // Use img.vietqr.io to generate QR
            $qrImageUrl = "https://img.vietqr.io/image/{$bankId}-{$accountNo}-{$template}.png?" . http_build_query([
                'amount' => $request->amount,
                'addInfo' => $description,
                'accountName' => $accountName
            ]);

            // Save checkout record
            $checkout = Checkout::create([
                'bookingID' => $request->bookingID,
                'paymentMethod' => 'bank_transfer',
                'amount' => $request->amount,
                'paymentStatus' => 'pending',
                'transactionID' => $orderId,
                'paymentData' => json_encode([
                    'qrImageUrl' => $qrImageUrl,
                    'accountNo' => $accountNo,
                    'accountName' => $accountName,
                    'bankId' => $bankId,
                    'description' => $description
                ])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'QR code generated successfully',
                'data' => [
                    'checkoutID' => $checkout->checkoutID,
                    'qrImageUrl' => $qrImageUrl,
                    'accountNo' => $accountNo,
                    'accountName' => $accountName,
                    'bankName' => 'VietinBank',
                    'bankId' => $bankId,
                    'amount' => $request->amount,
                    'description' => $description,
                    'orderId' => $orderId
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Create VietQR Payment Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'QR generation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/payment/vietqr/verify",
     *     summary="Verify VietQR payment (Manual verification)",
     *     tags={"Payment"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"checkoutID"},
     *             @OA\Property(property="checkoutID", type="integer", example=1),
     *             @OA\Property(property="transactionID", type="string", example="BANK_TXN_123456")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Payment verified"),
     *     @OA\Response(response=404, description="Checkout not found")
     * )
     */
    public function verifyVietQRPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'checkoutID' => 'required|integer|exists:checkout,checkoutID',
            'transactionID' => 'nullable|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $checkout = Checkout::find($request->checkoutID);

            if (!$checkout) {
                return response()->json([
                    'success' => false,
                    'message' => 'Checkout not found'
                ], 404);
            }

            // Admin manual verification
            // Trong thực tế, có thể tích hợp với webhook từ ngân hàng
            $checkout->update([
                'paymentStatus' => 'completed',
                'paymentDate' => now(),
                'transactionID' => $request->transactionID ?? $checkout->transactionID
            ]);

            // Update booking status
            $booking = $checkout->booking;
            if ($booking && $booking->bookingStatus == 'pending') {
                $booking->update(['bookingStatus' => 'confirmed']);
            }

            // Auto create invoice
            if ($booking && !$booking->invoice) {
                Invoice::create([
                    'bookingID' => $booking->bookingID,
                    'amount' => $checkout->amount,
                    'dateIssued' => now(),
                    'details' => json_encode([
                        'payment_method' => 'Bank Transfer (VietQR)',
                        'transaction_id' => $request->transactionID ?? $checkout->transactionID,
                        'payment_date' => now()->format('Y-m-d H:i:s'),
                        'verified_by' => 'admin'
                    ])
                ]);
                Log::info('Invoice created for bookingID: ' . $booking->bookingID);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment verified successfully',
                'data' => [
                    'checkoutID' => $checkout->checkoutID,
                    'bookingID' => $checkout->bookingID,
                    'paymentStatus' => $checkout->paymentStatus
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Verify VietQR Payment Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/payment/status/{checkoutID}",
     *     summary="Get payment status",
     *     tags={"Payment"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="checkoutID",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Payment status retrieved"),
     *     @OA\Response(response=404, description="Payment not found")
     * )
     */
    public function getPaymentStatus($checkoutID)
    {
        try {
            $checkout = Checkout::with('booking')->find($checkoutID);

            if (!$checkout) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'checkoutID' => $checkout->checkoutID,
                    'bookingID' => $checkout->bookingID,
                    'paymentMethod' => $checkout->paymentMethod,
                    'amount' => $checkout->amount,
                    'paymentStatus' => $checkout->paymentStatus,
                    'paymentDate' => $checkout->paymentDate,
                    'transactionID' => $checkout->transactionID,
                    'booking' => [
                        'bookingStatus' => $checkout->booking->bookingStatus ?? null,
                        'tourID' => $checkout->booking->tourID ?? null
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get Payment Status Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get payment status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/payment/history",
     *     summary="Get payment history for current user",
     *     tags={"Payment"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Payment history retrieved")
     * )
     */
    public function getPaymentHistory(Request $request)
    {
        try {
            $user = auth()->user();

            $payments = Checkout::with(['booking.tour'])
                ->whereHas('booking', function ($query) use ($user) {
                    $query->where('userID', $user->userID);
                })
                ->orderBy('checkoutID', 'desc')
                ->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $payments
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get Payment History Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get payment history: ' . $e->getMessage()
            ], 500);
        }
    }
}
