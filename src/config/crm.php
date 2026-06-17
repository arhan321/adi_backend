<?php

return [
    'business_name' => env('CRM_BUSINESS_NAME', 'Kopi Banget'),

    'twilio' => [
        'enabled' => env('TWILIO_WHATSAPP_ENABLED', false),
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'whatsapp_from' => env('TWILIO_WHATSAPP_FROM', '+14155238886'),
        'status_callback_url' => env('TWILIO_STATUS_CALLBACK_URL'),
    ],

    'retention' => [
        'default_send_time' => env('CRM_RETENTION_SEND_TIME', '07:00'),
        'chunk_size' => env('CRM_RETENTION_CHUNK_SIZE', 100),
    ],
];
