<?php

use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;

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
    ],

    'servers' => [
        'Live' => 'api',
    ],

    'middleware' => [
        'web',
    ],

    'extensions' => [],
];
