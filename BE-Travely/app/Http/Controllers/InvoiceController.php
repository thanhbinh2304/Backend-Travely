<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class InvoiceController extends Controller
{
    /**
     * GET /invoices/{id}
     * -> Xem chi tiết 1 hóa đơn
     */
    public function show($id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $invoice = Invoice::with(['booking.tour'])
                ->where('invoiceID', $id)
                ->whereHas('booking', function ($q) use ($user) {
                    $q->where('userID', $user->userID);
                })
                ->first();

            if (!$invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data'    => $invoice,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get invoice detail',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /invoices/{id}/download
     * -> Tải hóa đơn (tạm thời trả JSON, sau bạn có thể thay bằng PDF)
     */
    public function download($id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $invoice = Invoice::with(['booking.tour'])
                ->where('invoiceID', $id)
                ->whereHas('booking', function ($q) use ($user) {
                    $q->where('userID', $user->userID);
                })
                ->first();

            if (!$invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice not found',
                ], 404);
            }

            // TODO: sau này generate PDF tại đây
            return response()->json([
                'success' => true,
                'message' => 'Download invoice (stub)',
                'data'    => $invoice,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to download invoice',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
