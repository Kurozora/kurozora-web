<?php

namespace App\Spiders\MAL;

use App\Processors\MAL\AnimeCharacterProcessor;
use App\Processors\MAL\MangaCharacterProcessor;
use App\Spiders\MAL\Middleware\BackoffMiddleware;
use App\Spiders\MAL\Middleware\CircuitBreakerMiddleware;
use App\Spiders\MAL\Middleware\RateLimitMiddleware;
use App\Traits\Spider\ParsesAnimeCharacters;
use App\Traits\Spider\ParsesMangaCharacters;
use Generator;
use RoachPHP\Downloader\Middleware\RequestDeduplicationMiddleware;
use RoachPHP\Downloader\Middleware\UserAgentMiddleware;
use RoachPHP\Extensions\LoggerExtension;
use RoachPHP\Extensions\StatsCollectorExtension;
use RoachPHP\Http\Response;
use RoachPHP\Spider\BasicSpider;
use RoachPHP\Spider\ParseResult;

class CastSpider extends BasicSpider
{
    use ParsesAnimeCharacters;
    use ParsesMangaCharacters;

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
        AnimeCharacterProcessor::class,
        MangaCharacterProcessor::class,
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
     * The delay in seconds between request batches.
     *
     * @var int $requestDelay
     */
    public int $requestDelay = 4;

    /**
     * Parses the characters page of an anime or manga.
     *
     * @param Response $response
     *
     * @return Generator<ParseResult>
     */
    public function parse(Response $response): Generator
    {
        if (str($response->getUri())->contains('/manga/')) {
            yield from $this->parseMangaCharacters($response);
        } else {
            yield from $this->parseAnimeCharacters($response);
        }

        if ($response->getStatus() === 200 && isset($this->context['onParsed'])) {
            $this->context['onParsed']($response->getRequest()->getUri());
        }
    }
}
