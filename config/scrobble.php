<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Watched threshold
    |--------------------------------------------------------------------------
    |
    | The percentage of runtime a play must cross on `stop` before the server
    | commits it as watched.
    |
    */

    'watched_threshold' => env('SCROBBLE_WATCHED_THRESHOLD', 80),

    /*
    |--------------------------------------------------------------------------
    | Rewatch limit
    |--------------------------------------------------------------------------
    |
    | The maximum number of rewatch cycles a single episode accepts.
    |
    */

    'max_rewatch_count' => env('SCROBBLE_MAX_REWATCH_COUNT', 50),

    /*
    |--------------------------------------------------------------------------
    | Resolve timeout
    |--------------------------------------------------------------------------
    |
    | How many seconds the inline cache-miss scrape may block the request
    | before the event goes pending and resolution moves to the scrape queue.
    | Keeps a slow MAL fetch from tying up an Octane worker.
    |
    */

    'resolve_timeout' => env('SCROBBLE_RESOLVE_TIMEOUT', 5),
];
