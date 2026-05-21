<?php

namespace App\Spiders\MAL\Middleware;

use Psr\Log\LoggerInterface;
use RoachPHP\Downloader\Middleware\ResponseMiddlewareInterface;
use RoachPHP\Http\Response;
use RoachPHP\Support\Configurable;

class BackoffMiddleware implements ResponseMiddlewareInterface
{
    use Configurable;

    /**
     * The logger used to report backoff events.
     *
     * @var LoggerInterface $logger
     */
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Sleeps the worker when the upstream signals backpressure via a configured status.
     *
     * @param Response $response
     *
     * @return Response
     */
    public function handleResponse(Response $response): Response
    {
        $status = $response->getStatus();
        $backoffStatuses = $this->backoffStatuses();

        if (!isset($backoffStatuses[$status])) {
            return $response;
        }

        $sleepSeconds = $this->parseRetryAfter($response, (int) $backoffStatuses[$status]);
        $sleepSeconds = min($sleepSeconds, $this->maxBackoffSeconds());

        if ($sleepSeconds <= 0) {
            return $response;
        }

        $this->logger->warning('[BackoffMiddleware] Backing off after upstream signal', [
            'uri' => $response->getRequest()->getUri(),
            'status' => $status,
            'sleep' => $sleepSeconds,
            'retry_after_header' => $response->getResponse()->getHeaderLine('Retry-After'),
        ]);

        sleep($sleepSeconds);

        return $response;
    }

    /**
     * Parses the Retry-After header, falling back to the default if absent or malformed.
     *
     * @param Response $response
     * @param int      $default
     *
     * @return int
     */
    protected function parseRetryAfter(Response $response, int $default): int
    {
        $header = $response->getResponse()->getHeaderLine('Retry-After');

        if ($header === '') {
            return $default;
        }

        if (is_numeric($header)) {
            return max(0, (int) $header);
        }

        $timestamp = strtotime($header);

        if ($timestamp === false) {
            return $default;
        }

        return max(0, $timestamp - time());
    }

    /**
     * The default backoff seconds keyed by status code.
     *
     * @return array<int, int>
     */
    protected function backoffStatuses(): array
    {
        $statuses = $this->option('backoff_statuses');

        return is_array($statuses) && !empty($statuses) ? $statuses : [
            429 => 30,
            503 => 10,
        ];
    }

    /**
     * The hard ceiling on a single backoff sleep.
     *
     * @return int
     */
    protected function maxBackoffSeconds(): int
    {
        $max = $this->option('max_backoff_seconds');

        return is_int($max) && $max > 0 ? $max : 120;
    }

    /**
     * @return array
     */
    private function defaultOptions(): array
    {
        return [
            'backoff_statuses' => [
                429 => 30,
                503 => 10,
            ],
            'max_backoff_seconds' => 120,
        ];
    }
}
