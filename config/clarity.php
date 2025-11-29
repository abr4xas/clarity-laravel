<?php

declare(strict_types=1);

return [
    'id' => env('CLARITY_ID', 'XXXXXX'),
    'enabled' => env('CLARITY_ENABLED', true),
    'enabled_identify_api' => env('CLARITY_IDENTIFICATION_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Global Custom Tags
    |--------------------------------------------------------------------------
    |
    | Define global custom tags that will be automatically set for all
    | Clarity sessions. These tags can be used to filter sessions on
    | the Clarity dashboard. Format: ['key' => ['value1', 'value2']]
    |
    */
    'global_tags' => [],

    /*
    |--------------------------------------------------------------------------
    | Consent API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure Clarity Consent API for GDPR/CCPA compliance.
    | consent_version: 'v1' or 'v2' (v2 is recommended)
    | consent_required: Set to true if consent is required for your region
    |
    */
    'consent_version' => env('CLARITY_CONSENT_VERSION', 'v2'),
    'consent_required' => env('CLARITY_CONSENT_REQUIRED', false),

    /*
    |--------------------------------------------------------------------------
    | Auto-Tagging Configuration
    |--------------------------------------------------------------------------
    |
    | Automatically tag sessions with environment, routes, and user information.
    |
    */
    'auto_tag_environment' => env('CLARITY_AUTO_TAG_ENV', true),
    'auto_tag_routes' => env('CLARITY_AUTO_TAG_ROUTES', false),
    'auto_identify_users' => env('CLARITY_AUTO_IDENTIFY', false),
];
