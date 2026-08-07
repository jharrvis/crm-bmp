<?php

return [
    'provider' => env('MAP_PROVIDER', 'openstreetmap'),
    'tile_url' => env('MAP_TILE_URL', 'https://tile.openstreetmap.org/{z}/{x}/{y}.png'),
    'attribution' => env('MAP_TILE_ATTRIBUTION', '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'),
    'nominatim' => [
        'url' => env('MAP_NOMINATIM_URL', 'https://nominatim.openstreetmap.org/search'),
        'user_agent' => env('MAP_NOMINATIM_USER_AGENT', config('app.name').'/1.0 (+'.config('app.url').')'),
        'timeout' => (int) env('MAP_NOMINATIM_TIMEOUT', 8),
        'result_limit' => (int) env('MAP_NOMINATIM_RESULT_LIMIT', 5),
        'cache_days' => (int) env('MAP_NOMINATIM_CACHE_DAYS', 30),
    ],
];
