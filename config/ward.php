<?php

return [
    'enable_routes'  => env('LOG_VIEWER_ENABLE_ROUTES', false),
    'prefix'         => env('LOG_VIEWER_PREFIX', 'ward:'),
    'uri'            => env('LOG_VIEWER_URI', 'ward'),
    'middleware'     => env('LOG_VIEWER_MIDDLEWARE', 'web'),
    'logs_per_page'  => env('LOG_VIEWER_LOGS_PER_PAGE', 6),
    'cache_duration' => env('LOG_VIEWER_CACHE_DURATION', 0),
];
