<?php

return [

    /*
    |--------------------------------------------------------------------------
    | MoMo Payment Configuration
    |--------------------------------------------------------------------------
    */
    'momo' => [
        'partner_code' => env('MOMO_PARTNER_CODE', ''),
        'access_key' => env('MOMO_ACCESS_KEY', ''),
        'secret_key' => env('MOMO_SECRET_KEY', ''),
        'endpoint' => env('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create'),
        'return_url' => env('MOMO_RETURN_URL', ''),
        'notify_url' => env('MOMO_NOTIFY_URL', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | VietQR Configuration
    |--------------------------------------------------------------------------
    */
    'vietqr' => [
        'bank_id' => env('VIETQR_BANK_ID', '970422'),
        'account_no' => env('VIETQR_ACCOUNT_NO', ''),
        'account_name' => env('VIETQR_ACCOUNT_NAME', ''),
        'template' => env('VIETQR_TEMPLATE', 'compact'),
        'api_url' => env('VIETQR_API_URL', 'https://api.vietqr.io/v2/generate'),
    ],

];
