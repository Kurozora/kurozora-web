<?php

namespace App\Spiders\MAL;

use App\Models\Manga;
use App\Processors\MAL\UpcomingMangaProcessor;
use Generator;
use RoachPHP\Downloader\Middleware\RequestDeduplicationMiddleware;
use RoachPHP\Downloader\Middleware\UserAgentMiddleware;
use RoachPHP\Extensions\LoggerExtension;
use RoachPHP\Extensions\StatsCollectorExtension;
use RoachPHP\Http\Response;
use RoachPHP\Spider\BasicSpider;
use RoachPHP\Spider\ParseResult;
use Symfony\Component\DomCrawler\Crawler;

class UpcomingMangaSpider extends BasicSpider
{
    /**
     * @var array $startUrls
     */
    public array $startUrls = [
        'https://myanimelist.net/manga.php?o=9',
//        'https://myanimelist.net/manga.php?o=9&show=50',
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
        ]
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
        UpcomingMangaProcessor::class
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
        $ids = $response->filter('table[width="100%"][cellspacing="0"]')
            ->filter('tr')
            ->each(function (Crawler $item) {
                $sarea = $item->filter('td div[id^="sarea"]');

                if ($sarea->count()) {
                    $id = $sarea->attr('id');
                    return (int) str($id)->remove('sarea')->value();
                }

                return null;
            });
        $ids = array_values(array_filter($ids));

        if (isset($this->context['force']) && $this->context['force']) {
            // Scrape all found IDs
            $cleanIDs = $ids;
        } else {
            // Only interested in manga not in the database
            // So first get all from the database that match the IDs we scraped.
            // Then check for what's missing between scraped vs. what's found in database.
            $databaseIDs = Manga::withoutGlobalScopes()
                ->select(['mal_id'])
                ->whereIn('mal_id', $ids)
                ->pluck('mal_id')
                ->toArray();
            $cleanIDs = array_diff($ids, $databaseIDs);
        }

        yield $this->item($cleanIDs);
    }
}
