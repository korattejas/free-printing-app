<?php
// config/constants.php

return [
    "error_responses" => [
        'message' => 'Something went wrong. Please try again',
        'validator_error_code' => 422,
        'backend_error_code' => 500,
        'success_status_code' => 200,
    ],

    'DB_HOST' => env('DB_HOST', 'localhost'),
    'DB_USER' => env('DB_USER', 'your_username'),
    'DB_PASSWORD' => env('DB_PASSWORD', 'your_password'),
    'DB_NAME' => env('DB_NAME', 'your_database_name'),

    'SITE_NAME' => env('SITE_NAME', 'Your Site Name'),
    'BASE_URL' => env('BASE_URL', 'http://yourdomain.com'),

    'encryption_key' => env('APP_ENCRYPTION_KEY'),
];
