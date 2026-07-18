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
];
