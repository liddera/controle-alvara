<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
        'calendar_id' => env('GOOGLE_CALENDAR_ID', 'primary'),
        'timezone' => env('GOOGLE_CALENDAR_TIMEZONE', 'America/Porto_Velho'),
    ],

    'email_provider' => [
        'base_url' => env('EMAIL_PROVIDER_BASE_URL', 'https://api.brevo.com/v3'),
        'api_key' => env('EMAIL_PROVIDER_API_KEY'),
        'webhook_secret' => env('EMAIL_PROVIDER_WEBHOOK_SECRET'),
    ],

    'whatsapp_gateway' => [
        'provider' => env('WHATSAPP_GATEWAY_PROVIDER', 'http-v2'),
        'base_url' => env('WHATSAPP_GATEWAY_BASE_URL'),
        'api_key' => env('WHATSAPP_GATEWAY_API_KEY'),
        // url | base64 | auto
        'media_mode' => env('WHATSAPP_GATEWAY_MEDIA_MODE', 'auto'),
        'webhook_url' => env('WHATSAPP_GATEWAY_WEBHOOK_URL'),
        'webhook_secret' => env('WHATSAPP_GATEWAY_WEBHOOK_SECRET'),
    ],

];
