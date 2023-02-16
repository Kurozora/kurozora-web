<?php

namespace App\Spiders\MAL;

use App\Processors\MAL\MangaProcessor;
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

class MangaSpider extends BasicSpider
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
        MangaProcessor::class
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
        $id = basename($response->getUri());

        if ($response->getStatus() >= 400) {
            logger()->error($id);
            if ($response->getStatus() != 404) {
                logger()->warning($response->getStatus());
            }
            return $this->item([]);
        }

        logger()->channel('stderr')->info('🕷 [MAL_ID:MANGA:' . $id . '] Parsing response');
        $originalTitle = $response->filter('[itemprop="name"]')
            ->innerText();
        $attributes = $response->filter('div.leftside')
            ->filter('.spaceit_pad')
            ->each(function($item) {
                return str($item->text());
            });
        try {
            $synopsis = strip_tags($response->filter('[itemprop="description"]')
                ->html());
        } catch (Exception $exception) {
            $synopsis = null;
        }

        $studios = $response->filter('div.leftside a[href*="/manga/magazine/"]')
            ->each(function(Crawler $item) {
                $regex = '/(\d+)\//';
                $id = str($item->attr('href'))
                    ->remove(['/manga/magazine/'])
                    ->match($regex)
                    ->value();
                return [$id => $item->text()];
            });

        $genres = $response->filter('div.leftside a[href*="/manga/genre/"]')
            ->each(function(Crawler $item) {
                $regex = '/(\d+)\//';
                $id = str($item->attr('href'))
                    ->remove(['/manga/genre/'])
                    ->match($regex)
                    ->value();
                return [$id => $item->text()];
            });

        $authors = $response->filter('div.leftside a[href*="/people/"]')
            ->each(function(Crawler $item) {
                $regex = '/(\d+)\//';
                $id = str($item->attr('href'))
                    ->remove(['/people/'])
                    ->match($regex)
                    ->value();
                return [$id => $item->text()];
            });

        $imageUrl = $this->cleanImageUrl($response, 'div.leftside div a img[itemprop="image"]');

        logger()->channel('stderr')->info('✅️ [MAL_ID:MANGA:' . $id . '] Done parsing');

        yield $this->item([
            'id'                => $id,
            'original_title'    => $originalTitle,
            'attributes'        => $attributes,
            'synopsis'          => $synopsis,
            'image_url'         => $imageUrl,
            'studios'           => array_replace([], ...$studios),
            'genres'            => array_replace([], ...$genres),
            'authors'           => array_replace([], ...$authors),
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
}
