<?php

namespace App\Spiders;

use App\Processors\MALAnimeProcessor;
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

class MALAnimeSpider extends BasicSpider
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
        MALAnimeProcessor::class
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
        logger()->channel('stderr')->info('🕷 Parsing response');
        $id = basename($response->getUri());
        $originalTitle = $response->filter('h1.title-name')
            ->text();
        $attributes = $response->filter('div.leftside')
            ->filter('.spaceit_pad')
            ->each(function($item) {
                return str($item->text());
            });
        $synopsis = $response->filter('p[itemprop="description"]')
            ->text();

        $studios = $response->filter('div.leftside a[href*="/anime/producer/"]')
            ->each(function(Crawler $item) {
                $id = str_replace(['/anime/producer/', '/' . $item->text()], '', $item->attr('href'));
                return [$id => $item->text()];
            });
        $genres = $response->filter('div.leftside a[href*="/anime/genre/"]')
            ->each(function(Crawler $item) {
                $id = str_replace(['/anime/genre/', '/' . $item->text()], '', $item->attr('href'));
                return [$id => $item->text()];
            });

        $imageUrl = $this->cleanImageUrl($response, 'div.leftside div a img[itemprop="image"]');
        $videoUrl = $this->cleanVideoUrl($response, 'div.video-promotion a');
        $openings = $this->cleanSongs($response, 'div[class*="theme-songs opnening"] table'); // typo on the website
        $ending = $this->cleanSongs($response, 'div[class*="theme-songs ending"] table');

        logger()->channel('stderr')->info('✅️ Done parsing');
        yield $this->item([
            'id'                => $id,
            'original_title'    => $originalTitle,
            'attributes'        => $attributes,
            'synopsis'          => $synopsis,
            'image_url'         => $imageUrl,
            'video_url'         => $videoUrl,
            'studios'           => array_replace([], ...$studios),
            'genres'            => array_replace([], ...$genres),
            'openings'          => $openings,
            'ending'            => $ending,
        ]);
    }

    /**
     * Cleans dirty image URLs. For examples:
     *
     * https://cdn.myanimelist.net/r/80x120/images/manga/3/214566.jpg?s=48212bcd0396d503a01166149a29c67e => https://cdn.myanimelist.net/images/manga/3/214566.jpg
     * https://cdn.myanimelist.net/r/76x120/images/userimages/6098374.jpg?s=4b8e4f091fbb3ecda6b9833efab5bd9b => https://cdn.myanimelist.net/images/userimages/6098374.jpg
     * https://cdn.myanimelist.net/r/76x120/images/questionmark_50.gif?s=8e0400788aa6af2a2f569649493e2b0f => empty string
     *
     * @param Response $response
     * @param string|null $div
     * @return string|null
     */
    private function cleanImageUrl(Response $response, ?string $div): ?string
    {
        $imageUrl = $response->filter($div)
            ->attr('data-src');

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
     * @param string $div
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
     * Clean song response.
     *
     * @param Response $response
     * @param string $div
     * @return array
     */
    private function cleanSongs(Response $response, string $div): array
    {
        return $response->filter($div)
            ->last()
            ->filter('tr')
            ->each(function(Crawler $item, int $index) {
                $malSong = [];
                // Get position
                $malSong['position'] = $index + 1;

                // Get IDs
                $item->filter('td input')
                    ->each(function (Crawler $item) use (&$malSong) {
                        $id = str($item->attr('id'));

                        if ($id->startsWith('apple_url_')) {
                            // Get song MAL ID
                            $malSong['id'] = (int) $id->replace('apple_url_', '')->value();

                            // Get Apple Music ID
                            $regex = '/.+?i=/';
                            $amID = preg_replace($regex, '', $item->attr('value'));
                            $malSong['am_id'] = empty($amID) ? null : trim($amID);
                        } else {
                            $malSong['id'] = null;
                            $malSong['am_id'] = null;
                        }
                    });

                // Get title
                $regex = '/\".+\"/';
                preg_match($regex, $item->text(), $title);
                $title = empty($title) ? '' : trim($title[0]);

                // Get artist
                $regex = '/by.+/';
                preg_match($regex, $item->text(), $artist);
                $artist = empty($artist) ? '' : $artist[0];

                // Get episodes
                $regex = '/\(.+\)/';
                preg_match($regex, $artist, $episodes);
                $episodes = empty($episodes) ? '' : $episodes[0];
                $episodes = str($episodes)->remove(['(', 'eps', ')']);

                // Done with episode, clean artist string
                $artist = str($artist)->replaceMatches($regex, '')->remove(['by', ' ']);

                $malSong['title'] = empty($title) ? null : trim($title);
                $malSong['artist'] = empty($artist) ? null : trim($artist);
                $malSong['episodes'] = empty($episodes) ? null : trim($episodes);
                return $malSong;
            });
    }
}