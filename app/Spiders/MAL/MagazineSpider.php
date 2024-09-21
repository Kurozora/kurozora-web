<?php

namespace App\Spiders\MAL;

use App\Processors\MAL\MagazineProcessor;
use App\Spiders\MAL\Models\MagazineItem;
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

class MagazineSpider extends BasicSpider
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
        MagazineProcessor::class
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
        $id = basename(parse_url($response->getUri(), PHP_URL_PATH));
        $queryString = parse_url($response->getUri(), PHP_URL_QUERY) ?? 'page=1';
        parse_str($queryString, $queries);

        if ($response->getStatus() >= 400) {
            logger()->error('Magazine: ' . $id . ';status:' . $response->getStatus());

            return $this->item([]);
        }

        logger()->channel('stderr')->info('🕷 [MAL_ID:MAGAZINE:' . $id . '] Parsing response');
        $name = $response->filter('h1.h1')
            ->text();

        try {
            $mangas = $response->filter('.seasonal-anime')
                ->each(function (Crawler $item) {
                    $element = $item->filter('a[href^="https://myanimelist.net/manga"]');
                    $regex = '/manga\/(\d*)/';
                    $mangaID = str($element->attr('href'))
                        ->match($regex)
                        ->remove('/manga/')
                        ->value();
                    $title = strip_html($element->text());

                    return [
                        'id' => (int) $mangaID,
                        'title' => $title
                    ];
                });
        } catch (Exception $e) {
            $mangas = [];
        }

        try {
            $nextPage = $response->filter('.pagination a.current')
                ->nextAll()
                ->first()
                ->attr('href');
        } catch (Exception $e) {
            $nextPage = null;
        }

        yield $this->item(new MagazineItem(
            $id,
            $queries['page'],
            $name,
            $mangas
        ));

        // Next page
        if (!empty($nextPage)) {
            yield ParseResult::request('GET', $nextPage, [$this, 'parse']);
        }
    }
}
