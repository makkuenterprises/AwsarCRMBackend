<?php

return [
    'api_key' => env('ZOOM_API_KEY'),
    'api_secret' => env('ZOOM_API_SECRET'),
    'base_url' => env('ZOOM_API_URL', 'https://api.zoom.us/v2/'),
    'user_id' => env('ZOOM_USER_ID'), // Ensure this is set correctly
];