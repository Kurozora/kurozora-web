<?php

namespace App\Spiders\TVDB;

use App\Processors\TVDB\EpisodeProcessor;
use Exception;
use Generator;
use RoachPHP\Downloader\Middleware\RequestDeduplicationMiddleware;
use RoachPHP\Downloader\Middleware\UserAgentMiddleware;
use RoachPHP\Extensions\LoggerExtension;
use RoachPHP\Extensions\StatsCollectorExtension;
use RoachPHP\Http\Response;
use RoachPHP\Spider\BasicSpider;
use RoachPHP\Spider\ParseResult;
use Symfony\Component\DomCrawler\Crawler;

class EpisodeSpider extends BasicSpider
{
    /**
     * The TVDB ID being crawled.
     *
     * @var int $tvdbID
     */
    protected int $tvdbID = 0;

    /**
     * The list of found episodes.
     *
     * @var array $episodes
     */
    protected array $episodes = [];

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
        EpisodeProcessor::class
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
            $seasonUrl = $detailPageUrl . '/seasons/official/1';

            yield $this->request('GET', $seasonUrl, 'parseSeason');
        }

        logger()->channel('stderr')->info('✅️ [tvdb_id:' . $this->tvdbID . '] Done parsing');
    }

    /**
     * @param Response $response
     *
     * @return Generator<ParseResult>
     */
    public function parseSeason(Response $response): Generator
    {
        logger()->channel('stderr')->info('🕷 [tvdb_id:' . $this->tvdbID . '] Parsing season response');
        $this->episodes = $response->filter('table tbody')
            ->filter('tr')
            ->each(function (Crawler $item) {
                $tdElements = $item->filter('td');
                return $tdElements->eq(1)
                    ->filter('a')
                    ->link()
                    ->getUri();
            });

        try {
            foreach ($this->episodes as $episode) {
                yield $this->request('GET', $episode, 'parseEpisode');
            }

            logger()->channel('stderr')->info('✅️ [tvdb_id:' . $this->tvdbID . '] Done season parsing');
        } catch (Exception $e) {
            logger()->channel('stderr')->error('❌ [tvdb_id:' . $this->tvdbID . '] ' . $e->getMessage());
        }
    }

    /**
     * @param Response $response
     *
     * @return Generator<ParseResult>
     */
    public function parseEpisode(Response $response): Generator
    {
        logger()->channel('stderr')->info('🕷 [tvdb_id:' . $this->tvdbID . '] Parsing episode response');

        // Title and synopsis
        $translations = $response->filter('div#translations div[data-language]')
            ->each(function (Crawler $item) {
                $translation = [];
                $translation['code'] = $item->attr('data-language');
                $translation['title'] = $item->attr('data-title');
                $translation['synopsis'] = $item->text();
                return $translation;
            });

        // Breadcrumbs
        $breadcrumb = $response->filter('div.page-toolbar div.crumbs a[href*="seasons/official"]')
            ->ancestors()
            ->text();
        try {
            $absoluteBreadcrumb = $response->filter('div.page-toolbar div.crumbs a[href*="seasons/absolute"]')
                ->ancestors()
                ->text();
        } catch (Exception $exception) {
            $absoluteBreadcrumb = $breadcrumb;
        }

        // Season
        $seasonRegex = '/Season \d+/';
        $seasonNumber = str($breadcrumb)
            ->match($seasonRegex)
            ->value();

        // Episode
        $episodeRegex = '/Episode \d+/';
        $episodeNumber = str($breadcrumb)
            ->match($episodeRegex)
            ->value();
        $episodeNumberTotal = str($absoluteBreadcrumb)
            ->match($episodeRegex)
            ->value();

        // Episode duration
        try {
            $episodeDuration = $response->filter('strong:contains("Runtime")')
                ->ancestors()
                ->filter('span')
                ->text();
        } catch (Exception $exception) {
            $episodeDuration = null;
        }

        // Episode first aired
        try {
            $episodeStartedAt = $response->filter('strong:contains("Originally Aired")')
                ->ancestors()
                ->filter('span a')
                ->text();
        }  catch (Exception $exception) {
            $episodeStartedAt = null;
        }

        // Episode image
        try {
            $episodeBannerImageUrl = $response->filter('img[src*="/episode/"], img[src*="/episodes/"], img[src*="/series/"]')
                ->attr('src');
        } catch (Exception $exception) {
            $episodeBannerImageUrl = null;
        }

        try {
            yield $this->item([
                'tvdb_id' => $this->tvdbID,
                'translations' => $translations,
                'season_number' => $seasonNumber,
                'episode_number' => $episodeNumber,
                'episode_number_total' => $episodeNumberTotal,
                'episode_duration' => $episodeDuration,
                'episode_started_at' => $episodeStartedAt,
                'episode_banner_image_url' => $episodeBannerImageUrl,
            ]);

            logger()->channel('stderr')->info('✅️ [tvdb_id:' . $this->tvdbID . '] Done episode parsing');
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
        $headers = get_headers($url, 1);

        if ($headers !== false && isset($headers['Location'])) {
            if (is_string($headers['Location'])) {
                return $headers['Location'];
            }

            return $headers['Location'][count($headers['Location']) - 1];
        }

        return false;
    }
}
