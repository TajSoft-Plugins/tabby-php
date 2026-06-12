<?php

declare(strict_types=1);

return [
    'sandbox' => filter_var(env('IS_TABBY_SANDBOX', false), FILTER_VALIDATE_BOOL),

    'keys' => [
        'live' => [
            'secret_key' => env('TABBY_LIVE_SECRET_KEY'),
            'public_key' => env('TABBY_LIVE_PUBLIC_KEY'),
        ],
        'sandbox' => [
            'secret_key' => env('TABBY_SANDBOX_SECRET_KEY'),
            'public_key' => env('TABBY_SANDBOX_PUBLIC_KEY'),
        ],
    ],

    // Optional legacy single-key overrides.
    'secret_key' => env('TABBY_SECRET_KEY'),
    'public_key' => env('TABBY_PUBLIC_KEY'),

    'merchant_code' => env('TABBY_MERCHANT_CODE'),
    'region' => env('TABBY_REGION', 'ksa'),
    'base_url' => env('TABBY_BASE_URL'),
    'timeout' => (int) env('TABBY_TIMEOUT', 30),

    'http' => [
        'connect_timeout' => (int) env('TABBY_CONNECT_TIMEOUT', 10),
        'debug' => (bool) env('TABBY_HTTP_DEBUG', false),
    ],
];
