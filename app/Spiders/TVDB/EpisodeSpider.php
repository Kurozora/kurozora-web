<?php

namespace App\Spiders\TVDB;

use App\Processors\TVDB\EpisodeProcessor;
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
     * The list of start urls.
     *
     * @var list<string> $startUrls
     */
    public array $startUrls = [
        //
//        'https://www.thetvdb.com/?tab=series&id=353712',
//        'https://www.thetvdb.com/dereferrer/series/397934'
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
        ],
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
        EpisodeProcessor::class
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
     * The delay in seconds between request batches.
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

        $detailPageUrl = $this->getDetailsPageUrl($response->getUri());

        if (is_string($detailPageUrl)) {
            yield $this->request('GET', $detailPageUrl, 'parseSeasonsList');
        }
    }

    /**
     * @param Response $response
     *
     * @return Generator<ParseResult>
     */
    public function parseSeasonsList(Response $response): Generator
    {
        $detailPageUrl = rtrim(preg_replace('#/seasons/official/.*$#', '', $response->getUri()), '/');

        $seasonNumbers = $response->filter('a[href*="/seasons/official/"]')
            ->each(function (Crawler $a) {
                try {
                    return (int) preg_replace('#.*/seasons/official/(\d+).*#', '$1', $a->link()->getUri());
                } catch (Exception $e) {
                    return 0;
                }
            });
        $seasonNumbers = array_values(array_unique(array_filter($seasonNumbers, fn (int $number) => $number >= 0)));

        $tvdbSeasonsFilter = isset($this->context['tvdbSeasons']) && is_array($this->context['tvdbSeasons'])
            ? array_values(array_map('intval', $this->context['tvdbSeasons']))
            : [];

        if (!empty($tvdbSeasonsFilter)) {
            $seasonNumbers = array_values(array_intersect($seasonNumbers, $tvdbSeasonsFilter));
        }

        if (empty($seasonNumbers)) {
            logger()->channel('stderr')->warning('⚠️ [tvdb_id:' . $this->tvdbID . '] No official seasons found on detail page');
            return;
        }

        try {
            foreach ($seasonNumbers as $seasonNumber) {
                yield $this->request('GET', $detailPageUrl . '/seasons/official/' . $seasonNumber, 'parseSeason');
            }

            logger()->channel('stderr')->info('✅️ [tvdb_id:' . $this->tvdbID . '] Done parsing seasons list (' . count($seasonNumbers) . ' seasons queued)');
        } catch (Exception $e) {
            logger()->channel('stderr')->error('❌ [tvdb_id:' . $this->tvdbID . '] ' . $e->getMessage());
        }
    }

    /**
     * @param Response $response
     *
     * @return Generator<ParseResult>
     */
    public function parseSeason(Response $response): Generator
    {
        $seasonNumber = (int) preg_replace('#.*/seasons/official/(\d+).*#', '$1', $response->getUri());

        $this->episodes = $response->filter('table tbody')
            ->filter('tr')
            ->each(function (Crawler $item) {
                $tdElements = $item->filter('td');
                return $tdElements->eq(1)
                    ->filter('a')
                    ->link()
                    ->getUri();
            });

        logger()->channel('stderr')->info('🕷 [tvdb_id:' . $this->tvdbID . '] TVDB season ' . $seasonNumber . ': queueing ' . count($this->episodes) . ' episodes');

        try {
            foreach ($this->episodes as $episode) {
                yield $this->request('GET', $episode, 'parseEpisode');
            }
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
        if ($response->getStatus() >= 400) {
            logger()->error('Episode: ' . $response->getUri() . ';status:' . $response->getStatus());
            return $this->item([]);
        }

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
        try {
            $breadcrumb = $response->filter('div.page-toolbar div.crumbs a[href*="seasons/official"]')
                ->ancestors()
                ->text();
        } catch (Exception $exception) {
            logger()->channel('stderr')->warning('⚠️ [tvdb_id:' . $this->tvdbID . '] Missing official breadcrumb at ' . $response->getUri() . ' — skipping episode');
            return;
        }

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
