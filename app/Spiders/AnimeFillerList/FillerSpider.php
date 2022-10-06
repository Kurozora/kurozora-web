<?php

namespace App\Spiders\AnimeFillerList;

use App\Processors\AnimeFillerList\FillerProcessor;
use Generator;
use RoachPHP\Downloader\Middleware\RequestDeduplicationMiddleware;
use RoachPHP\Downloader\Middleware\UserAgentMiddleware;
use RoachPHP\Extensions\LoggerExtension;
use RoachPHP\Extensions\StatsCollectorExtension;
use RoachPHP\Http\Response;
use RoachPHP\Spider\BasicSpider;
use RoachPHP\Spider\ParseResult;
use Symfony\Component\DomCrawler\Crawler;

class FillerSpider extends BasicSpider
{
    /**
     * @var array $startUrls
     */
    public array $startUrls = [
        //
        'https://www.animefillerlist.com/shows/one-piece'
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
        FillerProcessor::class
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
        $fillerID = trim(basename($response->getUri()));
        logger()->channel('stderr')->info('🕷 [filler_id:' . $fillerID . '] Parsing response');

        $episodeList = $response->filter('table.EpisodeList tbody')
            ->filter('tr')
            ->each(function(Crawler $item) {
                $episode = [];
                $episode['number'] = $item->filter('td.Number')->text();
                $episode['type'] = $item->filter('td.Type')->text();
                return $episode;
            });

        foreach ($episodeList as $episode) {
            yield $this->item([
                'filler_id' => $fillerID,
                'episode_number' => $episode['number'],
                'filler_type' => $episode['type'],
            ]);
        }

        logger()->channel('stderr')->info('✅️ [filler_id:' . $fillerID . '] Done parsing');
    }
}
