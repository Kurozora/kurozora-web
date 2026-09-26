<?php

namespace App\Spiders\MAL;

use App\Processors\MAL\CharacterProcessor;
use App\Processors\MAL\PicturesProcessor;
use App\Spiders\MAL\Middleware\BackoffMiddleware;
use App\Spiders\MAL\Middleware\CircuitBreakerMiddleware;
use App\Spiders\MAL\Middleware\RateLimitMiddleware;
use App\Spiders\MAL\Models\CharacterItem;
use App\Traits\Spider\ParsesPictures;
use Exception;
use Generator;
use InvalidArgumentException;
use RoachPHP\Downloader\Middleware\RequestDeduplicationMiddleware;
use RoachPHP\Downloader\Middleware\UserAgentMiddleware;
use RoachPHP\Extensions\LoggerExtension;
use RoachPHP\Extensions\StatsCollectorExtension;
use RoachPHP\Http\Response;
use RoachPHP\Spider\BasicSpider;
use RoachPHP\Spider\ParseResult;
use Symfony\Component\DomCrawler\Crawler;

class CharacterSpider extends BasicSpider
{
    use ParsesPictures;

    public array $startUrls = [
        //
    ];

    public array $downloaderMiddleware = [
        RequestDeduplicationMiddleware::class,
        CircuitBreakerMiddleware::class,
        BackoffMiddleware::class,
        RateLimitMiddleware::class,
        [
            UserAgentMiddleware::class,
            ['userAgent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'],
        ]
    ];

    public array $spiderMiddleware = [
        //
    ];

    public array $itemProcessors = [
        CharacterProcessor::class,
        PicturesProcessor::class,
    ];

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
     * @return Generator<ParseResult>
     */
    public function parse(Response $response): Generator
    {
        $id = basename($response->getUri());

        if ($response->getStatus() >= 400) {
            logger()->error('Character: ' . $id . ';status:' . $response->getStatus());
            return $this->item([]);
        }

        logger()->channel('stderr')->debug('🕷 [MAL_ID:CHARACTER:' . $id . '] Parsing response');

        $nameNode = $response->filter('h2[class*="normal_header"]');

        if (!$nameNode->count()) {
            logger()->error('Character: ' . $id . ';status:' . $response->getStatus() . ';missing-title-node');
            return $this->item([]);
        }

        $name = $nameNode->innerText();
        $japaneseName = str($response
            ->filter('h2[class*="normal_header"] > span > small')
            ->text(''))
            ->trim('()')
            ->value();
        $alternativeNames = str($response->filter('h1')
            ->text(''))
            ->match('/"[^"]*"/')
            ->replace('"', '')
            ->explode(', ')
            ->toArray();
        $synopsis = str($response->filter('#content > table > tr > td:nth-child(2)')->html(''))
            ->replace('<br>', '\n')
            ->value();
        $synopsis = strip_html($this->removeChildNodes(
            (new Crawler($synopsis))
                ->filter('body')
        )->html(''));

        $imageURL = $this->cleanImageURL($response, 'a[href*="/pics"] img');

        $animes = collect($response->filter('div:contains(\'Animeography\') + table tr')
            ->each(function (Crawler $item) {
                $regex = '/(\d+)\//';
                $id = str($item->filter('td:nth-child(2) a[href*="/anime/"]')
                    ->attr('href', ''))
                    ->match($regex)
                    ->value();

                $name = $item->filter('td:nth-child(2) a')
                    ->text('');

                $role = $item->filter('small')
                    ->last()
                    ->text('');

                return [
                    'id' => $id,
                    'name' => $name,
                    'role' => $role,
                ];
            }))
            ->filter(fn (array $anime) => ! empty($anime['id']))
            ->values()
            ->all();

        $mangas = collect($response->filter('div:contains(\'Mangaography\') + table tr')
            ->each(function (Crawler $item) {
                $regex = '/(\d+)\//';
                $id = str($item->filter('td:nth-child(2) a[href*="/manga/"]')
                    ->attr('href', ''))
                    ->match($regex)
                    ->value();

                $name = $item->filter('td:nth-child(2) a')
                    ->text('');

                $role = $item->filter('small')
                    ->last()
                    ->text('');

                return [
                    'id' => $id,
                    'name' => $name,
                    'role' => $role
                ];
            }))
            ->filter(fn (array $manga) => ! empty($manga['id']))
            ->values()
            ->all();

        $people = collect($response->filter('div:contains(\'Voice Actors\') ~ table tr')
            ->each(function (Crawler $item) {
                $regex = '/(\d+)\//';
                $id = str($item->filter('a[href*="/people/"]')
                    ->attr('href', ''))
                    ->match($regex)
                    ->value();

                $name = $item->filter('a')
                    ->reduce(function (Crawler $crawler) {
                        return ! $crawler->filter('img')->count();
                    })
                    ->text('');

                $language = $item->filter('div small')
                    ->text('');

                return [
                    'id' => $id,
                    'name' => $name,
                    'language' => $language
                ];
            }))
            ->filter(fn (array $person) => ! empty($person['id']))
            ->values()
            ->all();

        yield $this->item(new CharacterItem(
            $id,
            $imageURL,
            $name,
            $japaneseName,
            $alternativeNames,
            $synopsis,
            $animes,
            $mangas,
            $people
        ));

        // Gallery
        $picturesPageLink = str(config('scraper.domains.mal.character_pictures'))
            ->replace(':x', $id)
            ->value();
        yield ParseResult::request('GET', $picturesPageLink, [$this, 'parsePictures']);
    }

    /**
     * Reads the profile image from the page and rejects MyAnimeList placeholders.
     */
    private function cleanImageURL(Response $response, ?string $div): ?string
    {
        try {
            $imageURL = $response->filter($div)
                ->attr('data-src');
        } catch (Exception $exception) {
            return null;
        }

        // If empty then return
        $imageURL = str(trim($imageURL));
        if (empty($imageURL)) {
            return null;
        }

        // Don't return placeholders
        $match = $imageURL->contains(['questionmark', 'qm_50', 'na.gif']);
        if ($match) {
            return null;
        }

        // Get base image url
        $cleanImageURL = $imageURL->replace('v.jpg', '.jpg');
        $cleanImageURL = $cleanImageURL->replace('t.jpg', '.jpg');
        $cleanImageURL = $cleanImageURL->replace('_thumb.jpg', '.jpg');
        $cleanImageURL = $cleanImageURL->value();

        // Remove queries and bs
        $regex = '/r\/\d{1,3}x\d{1,3}\//';
        $cleanImageURL = preg_replace($regex, '', $cleanImageURL);
        $regex = '/\?.+/';

        // Return clean url
        return preg_replace($regex, '', $cleanImageURL);
    }

    /**
     * Removes all HTML elements so the text is left over.
     *
     * @param Crawler $crawler
     *
     * @return Crawler
     * @throws InvalidArgumentException
     */
    public static function removeChildNodes(Crawler $crawler): Crawler
    {
        if (!$crawler->count()) {
            return $crawler;
        }

        $crawler->children()->each(
            function (Crawler $crawler) {
                $allowedNodes = ['p', 'i', 'b', 'br', 'strong', 'u'];
                $node = $crawler->getNode(0);

                if ($node === null || $node->nodeType === 3 || in_array($node->nodeName, $allowedNodes)) {
                    return;
                }

                $node->parentNode->removeChild($node);
            }
        );

        return $crawler;
    }
}
