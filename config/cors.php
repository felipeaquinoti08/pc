<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Only the public API paths need this - the admin panel itself is
    | first-party (same-origin session auth, no CORS needed). Restricted to
    | the CCITI site's own domains, since /api/contact-leads is meant to be
    | called from cciti.com.br, not from anywhere on the internet.
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['POST'],

    'allowed_origins' => [
        'https://cciti.com.br',
        'https://www.cciti.com.br',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'X-Requested-With', 'Accept'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
