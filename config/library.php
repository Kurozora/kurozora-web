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
    |--------------------------------------------------------------------------
    |
    | Number of seconds a queued state-change hint waits before it is published.
    |
    */

    'state_hint_delay_seconds' => env('LIBRARY_STATE_HINT_DELAY_SECONDS', 2),
];
