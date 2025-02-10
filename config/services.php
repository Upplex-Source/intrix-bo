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

    'mfa' => [
        'enabled' => env( 'MFA_ENABLED' ),
    ],

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'url' => [
        'admin' => env( 'ADMIN_URL' ),
        'admin_path' => env( 'ADMIN_PATH' ),
        'api' => env( 'API_URL' ),
        'crm' => env( 'CRM_URL' ),
    ],

    'app' => [
        'name' => env( 'APP_NAME' ),
    ],

    'mail' => [
        'receiver' => env( 'MAIL_RECEIVER' )
    ],

    'ipay88' => [
        'env' => env( 'IPAY88_ENV' ),
        'test_url' => env( 'IPAY88_TEST_URL' ),
        'merchant_code' => env( 'IPAY88_MERCHANT_CODE' ),
        'merchant_key' => env( 'IPAY88_MERCHANT_KEY' ),
        'staging_payment_url' => env( 'IPAY88_STAGING_PAYMENT_URL' ),
        'live_payment_url' => env( 'IPAY88_LIVE_PAYMENT_URL' ),
        'staging_callabck_url' => env( 'IPAY88_STAGING_CALLBACK' ),
        'live_callabck_url' => env( 'IPAY88_LIVE_CALLBACK' ),
        'staging_success_url' => env( 'IPAY88_STAGING_SUCCESS_URL' ),
        'live_success_url' => env( 'IPAY88_LIVE_SUCCESS_URL' ),
        'staging_failed_url' => env( 'IPAY88_STAGING_FAILED_URL' ),
        'live_failed_url' => env( 'IPAY88_LIVE_FAILED_URL' ),
    ]
];
