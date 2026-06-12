<?php

declare(strict_types=1);

return [
    'secret_key' => env('TABBY_SECRET_KEY'),
    'merchant_code' => env('TABBY_MERCHANT_CODE'),
    'region' => env('TABBY_REGION', 'ksa'),
    'base_url' => env('TABBY_BASE_URL'),
    'timeout' => (int) env('TABBY_TIMEOUT', 30),

    'http' => [
        'connect_timeout' => (int) env('TABBY_CONNECT_TIMEOUT', 10),
        'debug' => (bool) env('TABBY_HTTP_DEBUG', false),
    ],
];
