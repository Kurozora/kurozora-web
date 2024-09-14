<?php

namespace App\Spiders\TVDB;

use App\Processors\TVDB\BannerProcessor;
use Exception;
use Generator;
use RoachPHP\Downloader\Middleware\RequestDeduplicationMiddleware;
use RoachPHP\Downloader\Middleware\UserAgentMiddleware;
use RoachPHP\Extensions\LoggerExtension;
use RoachPHP\Extensions\StatsCollectorExtension;
use RoachPHP\Http\Response;
use RoachPHP\Spider\BasicSpider;
use RoachPHP\Spider\ParseResult;

class BannerSpider extends BasicSpider
{
    /**
     * The TVDB ID being crawled.
     *
     * @var int $tvdbID
     */
    protected int $tvdbID = 0;

    /**
     * @var array $startUrls
     */
    public array $startUrls = [
        //
//        'https://www.thetvdb.com/?tab=series&id=353712',
//        'https://www.thetvdb.com/dereferrer/series/397934'
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
        BannerProcessor::class
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
        $regex = '/\d+$/';
        preg_match($regex, $response->getUri(), $tvdbID);
        $this->tvdbID = $tvdbID[0] ?? 0;

        logger()->channel('stderr')->info('🕷 [tvdb_id:' . $this->tvdbID . '] Parsing response');

        $detailPageUrl = $this->getDetailsPageUrl($response->getUri());

        if (is_string($detailPageUrl)) {
            $bannerArtworksUrl = $detailPageUrl . '/artwork/backgrounds';

            yield $this->request('GET', $bannerArtworksUrl, 'parseBanner');
        }

        logger()->channel('stderr')->info('✅️ [tvdb_id:' . $this->tvdbID . '] Done parsing');
    }

    /**
     * @param Response $response
     *
     * @return Generator<ParseResult>
     */
    public function parseBanner(Response $response): Generator
    {
        logger()->channel('stderr')->info('🕷 [tvdb_id:' . $this->tvdbID . '] Parsing banner response');
        $imageURLs = $response->filter('img.lazy.img-responsive')->extract(['data-src']);

        try {
            yield $this->item([
                'tvdb_id' => $this->tvdbID,
                'image_urls' => $imageURLs,
            ]);

            logger()->channel('stderr')->info('✅️ [tvdb_id:' . $this->tvdbID . '] Done banner parsing');
        } catch (Exception $e) {
            logger()->channel('stderr')->error('❌ [tvdb_id:' . $this->tvdbID . '] ' . $e->getMessage());
        }
    }

    /**
     * Returns the actual details page url by following the redirect.
     *
     * @param string $url
     * @return string|bool
     */
    private function getDetailsPageUrl(string $url): string|bool
    {
        stream_context_set_default([
            'http' => [
                'method' => 'HEAD'
            ]
        ]);
        $headers = get_headers($url, true);

        if ($headers !== false && isset($headers['Location'])) {
            if (is_string($headers['Location'])) {
                return $headers['Location'];
            }

            return $headers['Location'][count($headers['Location']) - 1];
        }

        return false;
    }
}
