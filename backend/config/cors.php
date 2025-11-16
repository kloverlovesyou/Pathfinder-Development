<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'applications/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'https://pathfinder-development-production.up.railway.app',
        'https://ingenious-manifestation-production.up.railway.app'
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['Authorization'],

    'max_age' => 0,

    'supports_credentials' => true,
];