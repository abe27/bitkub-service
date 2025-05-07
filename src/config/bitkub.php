<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bitkub API Configuration
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for the Bitkub API.
    |
    */

    'api_key' => env('BITKUB_API_KEY', ''),
    'api_secret' => env('BITKUB_API_SECRET', ''),
    'endpoint' => env('BITKUB_ENDPOINT', 'https://api.bitkub.com'),
];
