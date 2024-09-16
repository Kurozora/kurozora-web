<?php

namespace App\Spiders\MAL;

use App\Enums\StudioType;
use App\Models\Studio;
use App\Processors\MAL\CompanyProcessor;
use App\Spiders\MAL\Models\CompanyItem;
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

class CompanySpider extends BasicSpider
{
    /**
     * The list of start urls.
     *
     * @var list<string> $startUrls
     */
    public array $startUrls = [
        'https://myanimelist.net/company'
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
        CompanyProcessor::class
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
    public int $concurrency = 1;

    /**
     * The delay (in seconds) between requests. Note that there
     * is no delay between concurrent requests. Instead, Roach
     * will wait for the `$requestDelay` before sending the
     * next "batch" of concurrent requests.
     *
     * @var int $requestDelay
     */
    public int $requestDelay = 3;

    /**
     * @param Response $response
     *
     * @return Generator<ParseResult>
     */
    public function parse(Response $response): Generator
    {
        $producerIDs = $response->filter('table tr td.company div a[href*="/anime/producer/"]')
            ->each(function (Crawler $item) {
                $regex = '/(\d+)\//';
                $link = $item->attr('href');

                return (int) str($link)
                    ->remove(['/anime/producer/'])
                    ->match($regex)
                    ->value();
            });
        $magazineIDs = $response->filter('table tr td.company div a[href*="/anime/magazine/"]')
            ->each(function (Crawler $item) {
                $regex = '/(\d+)\//';
                $link = $item->attr('href');

                return (int) str($link)
                    ->remove(['/manga/magazine/'])
                    ->match($regex)
                    ->value();
            });

        $producerIDs = array_values(array_filter($producerIDs));
        $magazineIDs = array_values(array_filter($magazineIDs));

        if (isset($this->context['force']) && $this->context['force']) {
            // Scrape all found IDs
            $cleanProducerIDs = $producerIDs;
            $cleanMagazineIDs = $magazineIDs;
        } else {
            // Only interested in studios not in the database
            // So first get all from the database that match the IDs we scraped.
            // Then check for what's missing between scraped vs. what's found in database.
            $producerDatabaseIDs = Studio::withoutGlobalScopes()
                ->select(['mal_id'])
                ->whereIn('mal_id', $producerIDs)
                ->where('type', '=', StudioType::Anime)
                ->pluck('mal_id')
                ->toArray();
            $cleanProducerIDs = array_diff($producerIDs, $producerDatabaseIDs);

            $magazineDatabaseIDs = Studio::withoutGlobalScopes()
                ->select(['mal_id'])
                ->whereIn('mal_id', $magazineIDs)
                ->where('type', '=', StudioType::Manga)
                ->pluck('mal_id')
                ->toArray();
            $cleanMagazineIDs = array_diff($magazineIDs, $magazineDatabaseIDs);
        }

        yield $this->item(new CompanyItem(
            $cleanProducerIDs,
            $cleanMagazineIDs
        ));
    }
}
