<?php

namespace App\Spiders\MAL;

use App\Models\Anime;
use App\Processors\MAL\TopAnimeProcessor;
use Generator;
use RoachPHP\Downloader\DownloaderMiddlewareInterface;
use RoachPHP\Downloader\Middleware\RequestDeduplicationMiddleware;
use RoachPHP\Downloader\Middleware\RequestMiddlewareInterface;
use RoachPHP\Downloader\Middleware\ResponseMiddlewareInterface;
use RoachPHP\Downloader\Middleware\UserAgentMiddleware;
use RoachPHP\Extensions\ExtensionInterface;
use RoachPHP\Extensions\LoggerExtension;
use RoachPHP\Extensions\StatsCollectorExtension;
use RoachPHP\Http\Response;
use RoachPHP\ItemPipeline\Processors\ItemProcessorInterface;
use RoachPHP\Spider\BasicSpider;
use RoachPHP\Spider\ParseResult;
use RoachPHP\Spider\SpiderMiddlewareInterface;
use Symfony\Component\DomCrawler\Crawler;

class TopAnimeSpider extends BasicSpider
{
    /**
     * The list of start urls.
     *
     * @var list<string> $startUrls
     */
    public array $startUrls = [
        'https://myanimelist.net/topanime.php',
//        'https://myanimelist.net/topanime.php?show=50',
    ];

    /**
     * The downloader middleware that should be used for runs of this spider.
     *
     * @var list<class-string<DownloaderMiddlewareInterface|RequestMiddlewareInterface|ResponseMiddlewareInterface>> $downloaderMiddleware
     */
    public array $downloaderMiddleware = [
        RequestDeduplicationMiddleware::class,
        [
            UserAgentMiddleware::class,
            ['userAgent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'],
        ]
    ];

    /**
     * The spider middleware that should be used for runs of this spider.
     *
     * @var list<class-string<SpiderMiddlewareInterface>> $spiderMiddleware
     */
    public array $spiderMiddleware = [
        //
    ];

    /**
     * The item processors that emitted items will be sent through.
     *
     * @var list<class-string<ItemProcessorInterface>> $itemProcessors
     */
    public array $itemProcessors = [
        TopAnimeProcessor::class
    ];

    /**
     * The extensions that should be used for runs of this spider.
     *
     * @var list<class-string<ExtensionInterface>> $extensions
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
        $ids = $response->filter('.anime_ranking_h3 a')
            ->each(function (Crawler $item) {
                $regex = '/\/anime\/(\d+)\//';
                $link = $item->attr('href');

                return str($link)
                    ->match($regex)
                    ->remove(['/anime/'])
                    ->value();
            });
        $ids = array_values(array_filter($ids));

        if (isset($this->context['force']) && $this->context['force']) {
            // Scrape all found IDs
            $cleanIDs = $ids;
        } else {
            // Only interested in anime not in the database
            // So first get all from the database that match the IDs we scraped.
            // Then check for what's missing between scraped vs. what's found in database.
            $databaseIDs = Anime::withoutGlobalScopes()
                ->select(['mal_id'])
                ->whereIn('mal_id', $ids)
                ->pluck('mal_id')
                ->toArray();
            $cleanIDs = array_diff($ids, $databaseIDs);
        }

        yield $this->item($cleanIDs);
    }
}
