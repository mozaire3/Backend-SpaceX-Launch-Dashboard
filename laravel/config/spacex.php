<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SpaceX API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for SpaceX API integration
    |
    */

    'api_base_url' => env('SPACEX_API_BASE_URL', 'https://api.spacexdata.com'),
    
    'timeout' => env('SPACEX_API_TIMEOUT', 30),
    
    'cache_time' => env('SPACEX_CACHE_TIME', 3600), // 1 hour in seconds
    
    'sync' => [
        'batch_size' => env('SPACEX_SYNC_BATCH_SIZE', 100),
        'delay_between_requests' => env('SPACEX_SYNC_DELAY', 1), // seconds
    ],
];