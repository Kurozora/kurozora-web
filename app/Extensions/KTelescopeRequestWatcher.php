<?php

namespace App\Extensions;

use Illuminate\Foundation\Http\Events\RequestHandled;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\Watchers\RequestWatcher;

class KTelescopeRequestWatcher extends RequestWatcher
{
    /**
     * The log levels that count as errors.
     *
     * @var array
     */
    private const array ERROR_LOG_LEVELS = ['error', 'critical', 'alert', 'emergency'];

    /**
     * Record an incoming HTTP request.
     *
     * @param RequestHandled $event
     *
     * @return void
     */
    public function recordRequest(RequestHandled $event): void
    {
        if (($this->options['errors_only'] ?? false) && !$this->isErrorRequest($event)) {
            return;
        }

        parent::recordRequest($event);
    }

    /**
     * Whether the entry is a crash, an error, or a failed request or job.
     *
     * @param IncomingEntry $entry
     *
     * @return bool
     */
    public static function isErrorEntry(IncomingEntry $entry): bool
    {
        if ($entry->isLog()) {
            return in_array($entry->content['level'] ?? null, self::ERROR_LOG_LEVELS, true);
        }

        return $entry->isReportableException() || $entry->isFailedRequest() || $entry->isFailedJob();
    }

    /**
     * Whether the request failed or recorded an error while being handled.
     *
     * @param RequestHandled $event
     *
     * @return bool
     */
    protected function isErrorRequest(RequestHandled $event): bool
    {
        if ($event->response->getStatusCode() >= 500) {
            return true;
        }

        foreach (Telescope::$entriesQueue as $entry) {
            if (self::isErrorEntry($entry)) {
                return true;
            }
        }

        return false;
    }
}
