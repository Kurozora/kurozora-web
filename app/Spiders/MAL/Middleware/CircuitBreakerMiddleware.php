<?php

namespace App\Spiders\MAL\Middleware;

use Psr\Log\LoggerInterface;
use RoachPHP\Downloader\Middleware\RequestMiddlewareInterface;
use RoachPHP\Downloader\Middleware\ResponseMiddlewareInterface;
use RoachPHP\Http\Request;
use RoachPHP\Http\Response;
use RoachPHP\Support\Configurable;

class CircuitBreakerMiddleware implements RequestMiddlewareInterface, ResponseMiddlewareInterface
{
    use Configurable;

    /**
     * The running count of consecutive upstream failures.
     *
     * @var int $consecutiveFailures
     */
    protected static int $consecutiveFailures = 0;

    /**
     * Whether the circuit has tripped open for the current process.
     *
     * @var bool $isOpen
     */
    protected static bool $isOpen = false;

    /**
     * The logger used to report drops and trip events.
     *
     * @var LoggerInterface $logger
     */
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Drops outbound requests once the circuit has tripped.
     *
     * @param Request $request
     *
     * @return Request
     */
    public function handleRequest(Request $request): Request
    {
        if (static::$isOpen) {
            return $request->drop('Circuit breaker open');
        }

        return $request;
    }

    /**
     * Counts upstream failures and trips the circuit at the configured threshold.
     *
     * @param Response $response
     *
     * @return Response
     */
    public function handleResponse(Response $response): Response
    {
        if (static::$isOpen) {
            return $response->drop('Circuit breaker open');
        }

        $status = $response->getStatus();

        if ($this->isFailureStatus($status)) {
            static::$consecutiveFailures++;

            if (static::$consecutiveFailures >= $this->threshold()) {
                static::$isOpen = true;
                $this->logger->error('[CircuitBreakerMiddleware] Tripped after ' . static::$consecutiveFailures . ' consecutive failures', [
                    'uri' => $response->getRequest()->getUri(),
                    'status' => $status,
                ]);

                return $response->drop('Circuit breaker tripped');
            }

            return $response;
        }

        static::$consecutiveFailures = 0;

        return $response;
    }

    /**
     * Resets the circuit state. Intended for tests.
     *
     * @return void
     */
    public static function reset(): void
    {
        static::$consecutiveFailures = 0;
        static::$isOpen = false;
    }

    /**
     * Whether the given HTTP status counts as an upstream failure.
     *
     * @param int $status
     *
     * @return bool
     */
    protected function isFailureStatus(int $status): bool
    {
        $failureStatuses = $this->option('failure_statuses');

        if (is_array($failureStatuses) && !empty($failureStatuses)) {
            return in_array($status, $failureStatuses, true);
        }

        return $status >= 500;
    }

    /**
     * The number of consecutive failures required to open the circuit.
     *
     * @return int
     */
    protected function threshold(): int
    {
        $threshold = $this->option('threshold');

        return is_int($threshold) && $threshold > 0 ? $threshold : 5;
    }

    /**
     * @return array
     */
    private function defaultOptions(): array
    {
        return [
            'threshold' => 5,
            'failure_statuses' => [],
        ];
    }
}
