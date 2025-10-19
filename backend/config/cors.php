<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['http://path.com','http://localhost:5173', 'https://portal.pathfinder.com'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];

