<?php

namespace App\Exceptions;

use App\Models\Timeout;
use Symfony\Component\HttpKernel\Exception\LockedHttpException;
use Throwable;

class UserTimedOutException extends LockedHttpException
{
    /**
     * The active timeout that caused the exception.
     *
     * @var Timeout
     */
    public Timeout $timeout;

    /**
     * Create a new exception instance.
     *
     * @param Timeout        $timeout
     * @param string         $message
     * @param Throwable|null $previous
     * @param int            $code
     * @param array          $headers
     */
    public function __construct(Timeout $timeout, string $message = '', ?Throwable $previous = null, int $code = 0, array $headers = [])
    {
        $this->timeout = $timeout;

        parent::__construct($message ?? $this->buildMessage($timeout), $previous, $code, $headers);
    }

    /**
     * Returns a localized description of the active timeout.
     *
     * @param Timeout $timeout
     *
     * @return string
     */
    private function buildMessage(Timeout $timeout): string
    {
        $reason = $timeout->reason_key->description;

        if ($timeout->is_permanent) {
            return __('Your account is permanently suspended for :reason.', ['reason' => $reason]);
        }

        return __('Your account is suspended until :expiry for :reason.', [
            'expiry' => $timeout->expires_at?->inUserTimezone()->locale(app()->getLocale())->isoFormat('LLL') ?? '',
            'reason' => $reason,
        ]);
    }
}
