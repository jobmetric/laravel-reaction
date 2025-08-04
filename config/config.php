<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Table Name
    |--------------------------------------------------------------------------
    |
    | Table name in database
    */

    "tables" => [
        'reaction' => 'reactions'
    ],

    /*
    |--------------------------------------------------------------------------
    | Prune Days
    |--------------------------------------------------------------------------
    |
    | Number of days to keep reactions before pruning.
    */

    "prune_days" => env('REACTION_PRUNE_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Header Names
    |--------------------------------------------------------------------------
    |
    | Header names for API requests.
    */

    "headers" => [
        'device_id' => env('REACTION_HEADER_DEVICE_ID', 'X-Device-ID'),
        'source' => env('REACTION_HEADER_SOURCE', 'X-Source'),
    ],

];
