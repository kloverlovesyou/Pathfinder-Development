<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'applications/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    // ✅ Allow all headers (including Authorization)
    'allowed_headers' => ['*'],

    // ✅ Make Authorization visible to browser / pass through proxy
    'exposed_headers' => ['Authorization'],

    'max_age' => 0,

    'supports_credentials' => true,
];