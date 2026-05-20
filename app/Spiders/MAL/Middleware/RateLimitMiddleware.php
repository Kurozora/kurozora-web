<?php

namespace App\Spiders\MAL\Middleware;

use Illuminate\Cache\RateLimiter;
use Psr\Log\LoggerInterface;
use RoachPHP\Downloader\Middleware\RequestMiddlewareInterface;
use RoachPHP\Http\Request;
use RoachPHP\Support\Configurable;

class RateLimitMiddleware implements RequestMiddlewareInterface
{
    use Configurable;

    /**
     * The Laravel rate limiter that backs the shared bucket.
     *
     * @var RateLimiter $rateLimiter
     */
    protected RateLimiter $rateLimiter;

    /**
     * The logger used to report dropped requests.
     *
     * @var LoggerInterface $logger
     */
    protected LoggerInterface $logger;

    public function __construct(RateLimiter $rateLimiter, LoggerInterface $logger)
    {
        $this->rateLimiter = $rateLimiter;
        $this->logger = $logger;
    }

    /**
     * Blocks the outbound request until a token is available in the shared bucket.
     *
     * @param Request $request
     *
     * @return Request
     */
    public function handleRequest(Request $request): Request
    {
        $key = $this->key();
        $maxAttempts = $this->maxAttempts();
        $decaySeconds = $this->decaySeconds();
        $maxWaitSeconds = $this->maxWaitSeconds();
        $elapsed = 0;

        while ($this->rateLimiter->tooManyAttempts($key, $maxAttempts)) {
            $waitSeconds = $this->rateLimiter->availableIn($key);

            if ($waitSeconds <= 0) {
                break;
            }

            if ($elapsed + $waitSeconds > $maxWaitSeconds) {
                $this->logger->warning('[RateLimitMiddleware] Dropped request after exceeding max wait time', [
                    'uri' => $request->getUri(),
                    'elapsed' => $elapsed,
                    'wait' => $waitSeconds,
                    'max_wait' => $maxWaitSeconds,
                ]);

                return $request->drop('Rate limit max wait exceeded');
            }

            sleep($waitSeconds);
            $elapsed += $waitSeconds;
        }

        $this->rateLimiter->hit($key, $decaySeconds);

        return $request;
    }

    /**
     * The cache key used for the shared bucket.
     *
     * @return string
     */
    protected function key(): string
    {
        $key = $this->option('key');

        return is_string($key) && $key !== '' ? $key : 'scraper:mal';
    }

    /**
     * The maximum number of requests allowed per decay window.
     *
     * @return int
     */
    protected function maxAttempts(): int
    {
        $maxAttempts = $this->option('max_attempts');

        return is_int($maxAttempts) && $maxAttempts > 0 ? $maxAttempts : 30;
    }

    /**
     * The decay window for the shared bucket, in seconds.
     *
     * @return int
     */
    protected function decaySeconds(): int
    {
        $decaySeconds = $this->option('decay_seconds');

        return is_int($decaySeconds) && $decaySeconds > 0 ? $decaySeconds : 60;
    }

    /**
     * The hard ceiling on cumulative wait time before a request is dropped.
     *
     * @return int
     */
    protected function maxWaitSeconds(): int
    {
        $maxWaitSeconds = $this->option('max_wait_seconds');

        return is_int($maxWaitSeconds) && $maxWaitSeconds > 0 ? $maxWaitSeconds : 300;
    }

    /**
     * @return array
     */
    private function defaultOptions(): array
    {
        $config = config('scraper.rate_limits.mal');

        return is_array($config) ? $config : [
            'key' => 'scraper:mal',
            'max_attempts' => 30,
            'decay_seconds' => 60,
            'max_wait_seconds' => 300,
        ];
    }
}
