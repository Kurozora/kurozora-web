<?php

namespace App\Spiders\MAL;

use App\Processors\MAL\VideoProcessor;
use App\Spiders\MAL\Middleware\CircuitBreakerMiddleware;
use App\Spiders\MAL\Middleware\BackoffMiddleware;
use App\Spiders\MAL\Middleware\RateLimitMiddleware;
use App\Traits\Spider\ParsesAnimeVideos;
use Generator;
use RoachPHP\Downloader\Middleware\RequestDeduplicationMiddleware;
use RoachPHP\Downloader\Middleware\UserAgentMiddleware;
use RoachPHP\Extensions\LoggerExtension;
use RoachPHP\Extensions\StatsCollectorExtension;
use RoachPHP\Http\Response;
use RoachPHP\Spider\BasicSpider;
use RoachPHP\Spider\ParseResult;

class AnimeVideoSpider extends BasicSpider
{
    use ParsesAnimeVideos;

    public array $startUrls = [
        //
    ];

    public array $downloaderMiddleware = [
        RequestDeduplicationMiddleware::class,
        CircuitBreakerMiddleware::class,
        BackoffMiddleware::class,
        RateLimitMiddleware::class,
        [
            UserAgentMiddleware::class,
            ['userAgent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'],
        ]
    ];

    public array $spiderMiddleware = [
        //
    ];

    public array $itemProcessors = [
        VideoProcessor::class
    ];

    public array $extensions = [
        LoggerExtension::class,
        StatsCollectorExtension::class,
    ];

    /**
     * How many requests are allowed to be sent concurrently.
     *
     * @var int $concurrency
     */
    public int $concurrency = 2;

    /**
     * The delay (in seconds) between requests. Note that there
     * is no delay between concurrent requests. Instead, Roach
     * will wait for the `$requestDelay` before sending the
     * next "batch" of concurrent requests.
     *
     * @var int $requestDelay
     */
    public int $requestDelay = 4;

    /**
     * @param Response $response
     *
     * @return Generator<ParseResult>
     */
    public function parse(Response $response): Generator
    {
        yield from $this->parseVideos($response);
    }
}
