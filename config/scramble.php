<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Scramble Config
    |--------------------------------------------------------------------------
    |
    | This option controls the default configuration of the Scramble package.
    |
    */

    'ui' => [
        'title' => 'Marketplace API',
        'path' => 'docs/api',
        'api_path' => 'api',
        'theme' => 'dark',
        'custom_css' => public_path('css/scramble-dark.css'),
    ],

    'servers' => [
        'Live' => 'api',
    ],

    'middleware' => [
        'web',
    ],

    'extensions' => [],
];
