<?php

namespace App\Spiders\MAL;

use App\Processors\MAL\CharacterProcessor;
use App\Spiders\MAL\Models\CharacterItem;
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
    public array $startUrls = [
        //
    ];

    public array $downloaderMiddleware = [
        RequestDeduplicationMiddleware::class,
        [
            UserAgentMiddleware::class,
            ['userAgent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'],
        ]
    ];

    public array $spiderMiddleware = [
        //
    ];

    public array $itemProcessors = [
        CharacterProcessor::class
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

        logger()->channel('stderr')->info('🕷 [MAL_ID:CHARACTER:' . $id . '] Parsing response');

        $name = $response->filter('h2[class="normal_header"]')
            ->innerText();
        $japaneseName = str($response
            ->filter('h2[class="normal_header"] > span > small')
            ->text())
            ->trim('()')
            ->value();
        $alternativeNames = str($response->filter('h1')
            ->text())
            ->match('/"[^"]*"/')
            ->replace('"', '')
            ->explode(', ')
            ->toArray();
        $synopsis = str($response->filter('#content > table > tr > td:nth-child(2)')->html())
            ->replace('<br>', '\n')
            ->value();
        $synopsis = strip_html($this->removeChildNodes(
            (new Crawler($synopsis))
                ->filter('body')
        )->html());

        $imageURL = $response->filter('meta[property="og:image"]')
            ->attr('content');

        $animes = $response->filter('div:contains(\'Animeography\') + table tr')
        ->each(function (Crawler $item) {
            $regex = '/(\d+)\//';
            $id = str($item->filter('td:nth-child(2) a[href*="/anime/"]')
                ->attr('href'))
                ->match($regex)
                ->value();

            $name = $item->filter('td:nth-child(2) a')
                ->text();

            $role = $item->filter('small')
                ->last()
                ->text();

            return [
                'id' => $id,
                'name' => $name,
                'role' => $role
            ];
        });

        $mangas = $response->filter('div:contains(\'Mangaography\') + table tr')
            ->each(function (Crawler $item) {
                $regex = '/(\d+)\//';
                $id = str($item->filter('td:nth-child(2) a[href*="/manga/"]')
                    ->attr('href'))
                    ->match($regex)
                    ->value();

                $name = $item->filter('td:nth-child(2) a')
                    ->text();

                $role = $item->filter('small')
                    ->last()
                    ->text();

                return [
                    'id' => $id,
                    'name' => $name,
                    'role' => $role
                ];
            });

        $people = $response->filter('div:contains(\'Voice Actors\') ~ table tr')
            ->each(function (Crawler $item) {
                $regex = '/(\d+)\//';
                $id = str($item->filter('a[href*="/people/"]')
                    ->attr('href'))
                    ->match($regex)
                    ->value();

                $name = $item->filter('a')
                    ->reduce(function (Crawler $crawler) {
                        return !$crawler->filter('img')->count();
                    })
                    ->text();

                $language = $item->filter('div small')
                    ->text();

                return [
                    'id' => $id,
                    'name' => $name,
                    'language' => $language
                ];
            });

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
