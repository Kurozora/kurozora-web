<?php

namespace App\Spiders\MAL;

use App\Processors\MAL\AnimeProcessor;
use App\Processors\MAL\AnimeStatsProcessor;
use App\Spiders\MAL\Models\AnimeItem;
use App\Spiders\MAL\Models\AnimeStatItem;
use Arr;
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

class AnimeSpider extends BasicSpider
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
        AnimeProcessor::class,
        AnimeStatsProcessor::class
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
    public int $requestDelay = 4;

    /**
     * @param Response $response
     *
     * @return Generator<ParseResult>
     */
    public function parse(Response $response): Generator
    {
        $id = basename($response->getUri());

        if ($response->getStatus() >= 400) {
            logger()->error('Anime: ' . $id);
            if ($response->getStatus() != 404) {
                logger()->warning('Anime: ' . $response->getStatus());
            }
            return $this->item([]);
        }

        logger()->channel('stderr')->info('🕷 [MAL_ID:ANIME:' . $id . '] Parsing response');
        $originalTitle = $response->filter('h1.title-name')
            ->text();
        $attributes = $response->filter('div.leftside')
            ->filter('.spaceit_pad')
            ->each(function ($item) {
                return str($item->text());
            });
        try {
            $synopsis = strip_tags($response->filter('[itemprop="description"]')
                ->html());
        } catch (Exception $exception) {
            $synopsis = null;
        }

        $studios = $response->filter('div.leftside a[href*="/anime/producer/"]')
            ->each(function (Crawler $item) {
                $regex = '/(\d+)\//';
                $id = str($item->attr('href'))
                    ->remove(['/anime/producer/'])
                    ->match($regex)
                    ->value();
                return [$id => $item->text()];
            });

        $genres = $response->filter('div.leftside a[href*="/anime/genre/"]')
            ->each(function (Crawler $item) {
                $regex = '/(\d+)\//';
                $id = str($item->attr('href'))
                    ->remove(['/anime/genre/'])
                    ->match($regex)
                    ->value();
                return [$id => $item->text()];
            });

        $imageUrl = $this->cleanImageUrl($response, 'div.leftside div a img[itemprop="image"]');
        $videoUrl = $this->cleanVideoUrl($response, 'div.video-promotion a');
        $relations = $this->cleanRelations($response, 'div.related-entries');
        $openings = $this->cleanSongs($response, 'div[class*="theme-songs opnening"] table'); // typo on the website
        $endings = $this->cleanSongs($response, 'div[class*="theme-songs ending"] table');

        logger()->channel('stderr')->info('✅️ [MAL_ID:ANIME:' . $id . '] Done parsing');

        yield $this->item(new AnimeItem(
            $id,
            $originalTitle,
            $attributes,
            $synopsis,
            $imageUrl,
            $videoUrl,
            array_replace([], ...$studios),
            array_replace([], ...$genres),
            $relations,
            $openings,
            $endings
        ));

        // Stats
        $statsPageLink = $response->filter('a[href*="/stats"]')
            ->attr('href');
        yield ParseResult::request('GET', $statsPageLink, [$this, 'parseStatsPage']);
    }

    /**
     * @param Response $response
     *
     * @return Generator<ParseResult>
     */
    public function parseStatsPage(Response $response): Generator
    {
        $regex = '/anime\/(\d*)/';
        $uri = str($response->getUri());
        $id = $uri->match($regex)->remove('/anime/')->value();
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

    /**
     * Cleans dirty image URLs. For examples:
     *
     * https://cdn.myanimelist.net/r/80x120/images/manga/3/214566.jpg?s=48212bcd0396d503a01166149a29c67e => https://cdn.myanimelist.net/images/manga/3/214566.jpg
     * https://cdn.myanimelist.net/r/76x120/images/userimages/6098374.jpg?s=4b8e4f091fbb3ecda6b9833efab5bd9b => https://cdn.myanimelist.net/images/userimages/6098374.jpg
     * https://cdn.myanimelist.net/r/76x120/images/questionmark_50.gif?s=8e0400788aa6af2a2f569649493e2b0f => empty string
     *
     * @param Response    $response
     * @param string|null $div
     *
     * @return string|null
     */
    private function cleanImageUrl(Response $response, ?string $div): ?string
    {
        try {
            $imageUrl = $response->filter($div)
                ->attr('data-src');
        } catch (Exception $exception) {
            return null;
        }

        // If empty then return
        $imageUrl = str(trim($imageUrl));
        if (empty($imageUrl)) {
            return null;
        }

        // Don't return placeholders
        $match = $imageUrl->contains(['questionmark', 'qm_50', 'na.gif']);
        if ($match) {
            return null;
        }

        // Get base image url
        $cleanImageUrl = $imageUrl->replace('v.jpg', '.jpg');
        $cleanImageUrl = $cleanImageUrl->replace('t.jpg', '.jpg');
        $cleanImageUrl = $cleanImageUrl->replace('_thumb.jpg', '.jpg');
        $cleanImageUrl = $cleanImageUrl->replace('userimages/thumbs', 'userimages');
        $cleanImageUrl = $cleanImageUrl->value();

        // Remove queries and bs
        $regex = '/r\/\d{1,3}x\d{1,3}\//';
        $cleanImageUrl = preg_replace($regex, '', $cleanImageUrl);
        $regex = '/\?.+/';

        // Return clean url
        return preg_replace($regex, '', $cleanImageUrl);
    }

    /**
     * Cleans dirty video URLs. For examples:
     *
     * https://www.youtube.com/embed/qig4KOK2R2g?enablejsapi=1&wmode=opaque&autoplay=1 => https://www.youtube.com/watch?v=qig4KOK2R2g
     * https://www.youtube.com/embed/j2hiC9BmJlQ?enablejsapi=1&wmode=opaque&autoplay=1 => https://www.youtube.com/watch?v=j2hiC9BmJlQ
     *
     * @param Response $response
     * @param string   $div
     *
     * @return string|null
     */
    private function cleanVideoUrl(Response $response, string $div): ?string
    {
        $videoURL = $response->filter($div);
        try {
            $videoURL = $videoURL->attr('href');
        } catch (Exception $e) {
            return null;
        }
        $videoURL = trim($videoURL);

        // Return if empty
        if (empty($videoURL)) {
            return null;
        }

        // Remove queries
        $regex = '/\?.+/';
        $clearVideoURL = str(preg_replace($regex, '', $videoURL));

        // Return clean url
        return $clearVideoURL->replace('embed/', 'watch?v=');
    }

    /**
     * Clean relations response.
     *
     * @param Response $response
     * @param string   $div
     *
     * @return array
     */
    private function cleanRelations(Response $response, string $div): array
    {
        $relations = [];

        try {
            $response->filter($div)
                ->children('div.entries-tile')
                ->children('.entry')
                ->each(function (Crawler $item, int $index) use (&$relations) {
                    $digitRegex = '/(\d+)\//';
                    $wordRegex = '/\/(\w+)\//';
                    $typeRegx = '/\(\w+.*\)/';

                    if ($item->filter('.relation')->count() === 0) {
                        return;
                    }

                    $relationType = str($item->filter('.relation')->innerText())
                        ->replaceMatches($typeRegx, '')
                        ->trim()
                        ->value();
                    $titleElement = $item->filter('div.title a');

                    $relations[$relationType][] = [
                        'mal_id' => str($titleElement->attr('href'))
                            ->match($digitRegex)
                            ->value(),
                        'type' => str($titleElement->attr('href'))
                            ->match($wordRegex)
                            ->value(),
                        'original_title' => str($titleElement->text())
                            ->trim()
                            ->value()
                    ];
                });
        } catch (Exception $e) {
        }

        try {
            $response->filter($div)
                ->filter('table[class*="entries-table"]')
                ->filter('tr')
                ->each(function (Crawler $item, int $index) use (&$relations) {
                    $relationType = str($item->children('td')->first()->innerText())
                        ->replaceLast(':', '')
                        ->value();

                    $item->children('td ul li')
                        ->children('a')
                        ->each(function (Crawler $item, int $index) use ($relationType, &$relations) {
                            $digitRegex = '/(\d+)\//';
                            $wordRegex = '/\/(\w+)\//';

                            $relations[$relationType][] = [
                                'mal_id' => str($item->attr('href'))
                                    ->match($digitRegex)
                                    ->value(),
                                'type' => str($item->attr('href'))
                                    ->match($wordRegex)
                                    ->value(),
                                'original_title' => str($item->text())
                                    ->trim()
                                    ->value()
                            ];
                        });
                });
        } catch (Exception $e) {
        }

        return $relations;
    }

    /**
     * Clean song response.
     *
     * @param Response $response
     * @param string   $div
     *
     * @return array
     */
    private function cleanSongs(Response $response, string $div): array
    {
        return $response->filter($div)
            ->last()
            ->filter('tr')
            ->each(function (Crawler $item, int $index) {
                $malSong = [];
                // Get position
                $malSong['position'] = $index + 1;

                // Get IDs
                $item->filter('td input')
                    ->each(function (Crawler $item) use (&$malSong) {
                        $id = str($item->attr('id'));

                        if ($id->startsWith('amazon_url_')) {
                            // Get song MAL ID
                            if (empty($malSong['mal_id'])) {
                                $malSong['mal_id'] = (int) $id->replace('amazon_url_', '')->value();
                            }

                            // Get Apple Music ID
                            $regex = '/.+?albums\/(.+?)\?.+?(trackAsin=B[\dA-Z]{9}|\d{9}(X|\d))/i';
                            preg_match($regex, $item->attr('value'), $amazonID);
                            $malSong['amazon_id'] = empty($amazonID) ? null : trim($amazonID[1] . '&' . $amazonID[2]);
                        } else if ($id->startsWith('apple_url_')) {
                            // Get song MAL ID
                            if (empty($malSong['mal_id'])) {
                                $malSong['mal_id'] = (int) $id->replace('apple_url_', '')
                                    ->value();
                            }

                            // Get Apple Music ID
                            $regex = '/.+?i=/';
                            $amID = preg_replace($regex, '', $item->attr('value'));
                            $malSong['am_id'] = empty($amID) || !is_numeric($amID) ? null : trim($amID);
                        } else if ($id->startsWith('spotify_url_')) {
                            // Get song MAL ID
                            if (empty($malSong['mal_id'])) {
                                $malSong['mal_id'] = (int) $id->replace('spotify_url_', '')
                                    ->value();
                            }

                            // Get Apple Music ID
                            $regex = '/.+?track\//';
                            $spotifyID = preg_replace($regex, '', $item->attr('value'));
                            $malSong['spotify_id'] = empty($spotifyID) ? null : trim($spotifyID);
                        } else if ($id->startsWith('youtube_url_')) {
                            // Get song MAL ID
                            if (empty($malSong['mal_id'])) {
                                $malSong['mal_id'] = (int) $id->replace('youtube_url_', '')
                                    ->value();
                            }

                            // Get Apple Music ID
                            $regex = '/(.*?)(^|\/|v=)([a-z0-9_-]{11})(.*)?/i';
                            preg_match($regex, $item->attr('value'), $youtubeID);
                            $malSong['youtube_id'] = empty($youtubeID[3]) ? null : trim($youtubeID[3]);
                        } else {
                            $malSong['mal_id'] = null;
                            $malSong['amazon_id'] = null;
                            $malSong['am_id'] = null;
                            $malSong['spotify_id'] = null;
                            $malSong['youtube_id'] = null;
                        }
                    });

                // Get title
                $regex = '/\".+\"/';
                preg_match($regex, $item->text(), $title);
                $title = empty($title) ? '' : trim($title[0]);
                // Here we `replaceFirst/Last` instead of `remove`, so we don't
                // accidentally remove quotes that are part of the official title.
                $title = str($title)
                    ->replaceFirst('"', '')
                    ->replaceLast('"', '');

                // Get artist
                $regex = '/by.+/';
                preg_match($regex, $item->text(), $artist);
                $artist = empty($artist) ? '' : $artist[0];

                // Get episodes
                $regex = '/\(([^)]+)\)$/';
                preg_match($regex, $artist, $episodes);
                $episodes = empty($episodes) ? '' : $episodes[0];
                $episodes = str($episodes)->remove(['(', 'eps', 'ep', ')']);

                // Done with episode, clean artist string
                $artist = str($artist)
                    ->replaceMatches($regex, '')
                    ->remove(['by', ' ']);

                $malSong['title'] = empty($title) ? null : trim($title);
                $malSong['artist'] = empty($artist) ? null : trim($artist);
                $malSong['episodes'] = empty($episodes) ? null : trim($episodes);
                return $malSong;
            });
    }
}
