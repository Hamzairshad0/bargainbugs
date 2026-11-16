<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    'ebay' => [
        'env' => env('EBAY_ENV', 'production'),           // production | sandbox
        'site_id' => env('EBAY_SITE_ID', 3),              // 3 = eBay UK
        'compat_level' => env('EBAY_COMPAT_LEVEL', 1349),
        'client_id' => env('EBAY_CLIENT_ID'),             // "App ID" in the developer portal
        'client_secret' => env('EBAY_CLIENT_SECRET'),     // "Cert ID"
        'dev_id' => env('EBAY_DEV_ID'),
        'runame' => env('EBAY_RUNAME'),                   // RuName (eBay Redirect URL name)
        'refresh_token' => env('EBAY_REFRESH_TOKEN'),     // optional: set here OR use "Connect eBay" in admin
        'seller' => env('EBAY_SELLER_USERNAME', 'bargainbugs'),
        'scopes' => env('EBAY_SCOPES', 'https://api.ebay.com/oauth/api_scope https://api.ebay.com/oauth/api_scope/sell.inventory.readonly https://api.ebay.com/oauth/api_scope/sell.account.readonly'),
    ],

    'admin' => [
        'email' => env('ADMIN_EMAIL', 'admin@bargainbugs.co.uk'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    'cron_secret' => env('CRON_SECRET'),

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
