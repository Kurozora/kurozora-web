<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Files order
    |--------------------------------------------------------------------------
    | Allowed: name_asc(A-Z), name_desc(Z-A), newest, oldest
    |
    */

    'files_order' => env('LOG_VIEWER_FILES_ORDER', 'newest'),

    'per_page' => env('LOG_VIEWER_PER_PAGE', 25),

    /*
    |--------------------------------------------------------------------------
    | Include file patterns
    |--------------------------------------------------------------------------
    |
    */

    'include_files' => ['*.log'],

    /*
    |--------------------------------------------------------------------------
    | Exclude file patterns.
    |--------------------------------------------------------------------------
    | This will take precedence over included files.
    |
    */

    'exclude_files' => [
        //'my_secret.log'
    ],

    /*
    |--------------------------------------------------------------------------
    |  Shorter stack trace filters.
    |--------------------------------------------------------------------------
    | Lines containing any of these strings will be excluded from the full log.
    | This setting is only active when the fuction is enabled via the user interface.
    |
    */

    'shorter_stack_trace_excludes' => [
        '/vendor/symfony/',
        '/vendor/laravel/framework/',
        '/vendor/barryvdh/laravel-debugbar/',
    ],
];
