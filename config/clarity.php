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
];
