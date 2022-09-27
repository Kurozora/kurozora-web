<?php

namespace App\Spiders;

use App\Processors\AnimixPlayAnimeProcessor;
use Exception;
use Generator;
use RoachPHP\Downloader\Middleware\ExecuteJavascriptMiddleware;
use RoachPHP\Downloader\Middleware\RequestDeduplicationMiddleware;
use RoachPHP\Downloader\Middleware\UserAgentMiddleware;
use RoachPHP\Extensions\LoggerExtension;
use RoachPHP\Extensions\StatsCollectorExtension;
use RoachPHP\Http\Response;
use RoachPHP\Spider\BasicSpider;
use RoachPHP\Spider\ParseResult;

class AnimixPlaySpider extends BasicSpider
{
    /**
     * @var array $startUrls
     */
    public array $startUrls = [
        //
    ];

    /**
     * The downloader middleware that should be used for runs of this spider.
     *
     * @var array|string[] $downloaderMiddleware
     */
    public array $downloaderMiddleware = [
        RequestDeduplicationMiddleware::class,
        [
            UserAgentMiddleware::class,
            ['userAgent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'],
        ],
        ExecuteJavascriptMiddleware::class
    ];

    /**
     * The spider middleware that should be used for runs of this spider.
     *
     * @var array $spiderMiddleware
     */
    public array $spiderMiddleware = [
        //
    ];

    /**
     * The item processors that emitted items will be sent through.
     *
     * @var array $itemProcessors
     */
    public array $itemProcessors = [
        AnimixPlayAnimeProcessor::class
    ];

    /**
     * The extensions that should be used for runs of this spider.
     *
     * @var array|string[] $extensions
     */
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
    public int $requestDelay = 1;

    /**
     * @param Response $response
     *
     * @return Generator<ParseResult>
     */
    public function parse(Response $response): Generator
    {
        logger()->channel('stderr')->info('🕷 Parsing response');
        $uri = trim(parse_url($response->getUri(), PHP_URL_PATH));
        $playerType = $response
            ->filter('span.altsourcenotif')
            ->text();

        if (str_contains($playerType, 'Internal')) {
            try {
                $url = $response->filter('iframe[src*="/api/"]')
                    ->first()
                    ->attr('src');
                $videoUrl = $this->getVideoUrl(config('scraper.domains.animix_play.base') . $url);

                logger()->channel('stderr')->info('✅️ Done parsing');
                yield $this->item([
                    'uri' => $uri,
                    'video_url' => $videoUrl,
                ]);
            } catch (Exception $e) {
                logger()->error($e->getMessage());
            }
        } else {
            logger()->channel('stderr')->error('❌️ No internal player found');
        }
    }

    /**
     * Returns the actual video url by following the redirect.
     *
     * @param string $url
     * @return string|bool
     */
    private function getVideoUrl(string $url): string|bool
    {
        stream_context_set_default([
            'http' => [
                'method' => 'HEAD'
            ]
        ]);
        $headers = get_headers($url, 1);

        if ($headers !== false && isset($headers['Location'])) {
            return $headers['Location'];
        }

        return false;
    }
}
