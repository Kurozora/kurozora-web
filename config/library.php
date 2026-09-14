<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tombstone retention
    |--------------------------------------------------------------------------
    |
    | Number of days a soft-deleted library entry is retained before permanent removal.
    |
    */

    'tombstone_retention_days' => env('LIBRARY_TOMBSTONE_RETENTION_DAYS', 90),

    /*
    |--------------------------------------------------------------------------
    | Import progress interval
    |--------------------------------------------------------------------------
    |
    | Minimum number of seconds between two progress broadcasts of one library import job.
    |
    */

    'import_progress_interval_seconds' => env('LIBRARY_IMPORT_PROGRESS_INTERVAL_SECONDS', 1),

    /*
    |--------------------------------------------------------------------------
    | Reminder window
    |--------------------------------------------------------------------------
    |
    | Number of days ahead the episode sync stream carries.
    |
    */

    'reminder_window_days' => env('LIBRARY_REMINDER_WINDOW_DAYS', 14),
];
