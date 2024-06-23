<?php

namespace App\Spiders\MAL;

use App\Processors\MAL\AnimeCharacterProcessor;
use App\Spiders\MAL\Models\AnimeCharacterItem;
use Generator;
use RoachPHP\Downloader\Middleware\UserAgentMiddleware;
use RoachPHP\Extensions\LoggerExtension;
use RoachPHP\Extensions\StatsCollectorExtension;
use RoachPHP\Http\Response;
use RoachPHP\Spider\BasicSpider;
use RoachPHP\Spider\ParseResult;
use Symfony\Component\DomCrawler\Crawler;

class AnimeCharacterSpider extends BasicSpider
{
    public array $startUrls = [
        //
    ];

    public array $downloaderMiddleware = [
        [
            UserAgentMiddleware::class,
            ['userAgent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'],
        ]
    ];

    public array $spiderMiddleware = [
        //
    ];

    public array $itemProcessors = [
        AnimeCharacterProcessor::class
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
        $regex = '/anime\/(\d*)/';
        $uri = str($response->getUri());
        $id = $uri->match($regex)->remove('/anime/')->value();
        logger()->channel('stderr')->info('🕷 [MAL_ID:ANIME:' . $id . '] Parsing character response');

        $cast = $response->filter('table[class*="anime-character-table"]')
            ->each(function (Crawler $item) {
                $regex = '/character\/(\d*)/';
                $characterID = str($item->filter('a[href^="https://myanimelist.net/character"]')->attr('href'))
                    ->match($regex)
                    ->remove('/character/')
                    ->value();
                $characterData = $item->filter('td')
                    ->eq(1)
                    ->children('.spaceit_pad');
                $characterName = str($characterData->eq(0)->text())
                    ->trim()
                    ->value();
                $castRole = str($characterData->eq(1)->text())
                    ->trim()
                    ->value();

                $actors = $item->filter('td')
                    ->eq(2)
                    ->children('table tr')
                    ->each(function (Crawler $item) {
                        $regex = '/people\/(\d*)/';
                        $personID = str($item->filter('a[href^="https://myanimelist.net/people"]')->attr('href'))
                            ->match($regex)
                            ->remove('/people/')
                            ->value();
                        $personName = str($item->filter('a[href^="https://myanimelist.net/people"]')->text())
                            ->trim()
                            ->value();
                        $language = str($item->filter('[class*="character-language"]')->text())
                            ->trim()
                            ->value();;

                        return [
                            'id' => $personID,
                            'name' => $personName,
                            'language' => $language,
                        ];
                    });

                return [
                    'character' => [
                        'id' => $characterID,
                        'name' => $characterName,
                    ],
                    'cast_role' => $castRole,
                    'actors' => $actors
                ];
            });

        $staff = $response->filter('h2:contains("Staff")')
            ->ancestors()
            ->nextAll()
            ->each(function (Crawler $item) {
                $staffData = $item->filter('td')
                    ->eq(1);
                $regex = '/people\/(\d*)/';
                $staffID = str($staffData->filter('a[href^="https://myanimelist.net/people"]')->attr('href'))
                    ->match($regex)
                    ->remove('/people/')
                    ->value();
                $staffName = $staffData->filter('a[href^="https://myanimelist.net/people"]')
                    ->text();
                $staffRole = $staffData->filter('small')
                    ->text();

                return [
                    'id' => $staffID,
                    'name' => $staffName,
                    'role' => $staffRole,
                ];
            });

        yield $this->item(new AnimeCharacterItem($id, $cast, $staff));
    }
}
