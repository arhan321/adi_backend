<?php

return [
    'business_name' => env('CRM_BUSINESS_NAME', 'Kopi Banget'),

    'whatsapp' => [
        'provider' => env('CRM_WHATSAPP_PROVIDER', 'fonnte'),
    ],

    'fonnte' => [
        'enabled' => env('FONNTE_WHATSAPP_ENABLED', false),
        'token' => env('FONNTE_TOKEN'),
        'base_url' => env('FONNTE_BASE_URL', 'https://api.fonnte.com'),
        'send_endpoint' => env('FONNTE_SEND_ENDPOINT', '/send'),
        'country_code' => env('FONNTE_COUNTRY_CODE', '62'),
        'device' => env('FONNTE_DEVICE'),
        'delay' => env('FONNTE_DELAY'),
        'timeout' => env('FONNTE_TIMEOUT', 30),
    ],

    'retention' => [
        'default_send_time' => env('CRM_RETENTION_SEND_TIME', '07:00'),
        'chunk_size' => env('CRM_RETENTION_CHUNK_SIZE', 100),
    ],
];
