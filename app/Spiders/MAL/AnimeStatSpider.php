<?php

namespace App\Spiders\MAL;

use App\Processors\MAL\AnimeStatsProcessor;
use App\Spiders\MAL\Models\AnimeStatItem;
use Arr;
use Exception;
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

class AnimeStatSpider extends BasicSpider
{
    /**
     * The list of start urls.
     *
     * @var list<string> $startUrls
     */
    public array $startUrls = [
        //
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
        AnimeStatsProcessor::class
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
    public int $requestDelay = 4;

    /**
     * @param Response $response
     *
     * @return Generator<ParseResult>
     */
    public function parse(Response $response): Generator
    {
        $regex = '/anime\/(\d*)/';
        $uri = str($response->getUri());
        $id = $uri->match($regex)->remove('/anime/')->value();

        if ($response->getStatus() >= 400) {
            logger()->error('Anime Stat: ' . $id . ';status:' . $response->getStatus());
            return $this->item([]);
        }

        logger()->channel('stderr')->info('🕷 [MAL_ID:ANIME:' . $id . '] Parsing stats response');

        $scores = $response->filter('table.score-stats tr')
            ->each(function (Crawler $item) {
                $scoreLabel = $item->filter('td.score-label')->text();
                $score = $item->filter('td small')->text();

                return [
                    "rating_$scoreLabel" => $score
                ];
            });
        $scores = Arr::collapse($scores);

        try {
            $scoreAverage = $response->filter('.leftside .score-label')
                ->text();
        } catch (Exception $e) {
            $scoreAverage = 'N/A';
        }

        $scoreCount = $scoreAverage !== 'N/A' ? $response->filter('span[itemprop="ratingCount"]')->text() : 0;

        yield $this->item(new AnimeStatItem(
            $id, $scores,
            (float) $scoreAverage,
            (int) $scoreCount
        ));
    }
}
