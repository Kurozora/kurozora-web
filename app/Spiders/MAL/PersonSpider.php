<?php

namespace App\Spiders\MAL;

use App\Processors\MAL\PersonProcessor;
use App\Processors\MAL\PicturesProcessor;
use App\Spiders\MAL\Middleware\BackoffMiddleware;
use App\Spiders\MAL\Middleware\CircuitBreakerMiddleware;
use App\Spiders\MAL\Middleware\RateLimitMiddleware;
use App\Spiders\MAL\Models\PersonItem;
use App\Traits\Spider\ParsesPictures;
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

class PersonSpider extends BasicSpider
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
        PersonProcessor::class,
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
     * @throws DdException
     */
    public function parse(Response $response): Generator
    {
        $id = basename($response->getUri());

        if ($response->getStatus() >= 400) {
            logger()->error('Person: ' . $id . ';status:' . $response->getStatus());
            return $this->item([]);
        }

        logger()->channel('stderr')->debug('🕷 [MAL_ID:PERSON:' . $id . '] Parsing response');

        $nameNode = $response->filter('h1.title-name');

        if (!$nameNode->count()) {
            logger()->error('Person: ' . $id . ';status:' . $response->getStatus() . ';missing-title-node');
            return $this->item([]);
        }

        $imageURL = $this->cleanImageURL($response, 'a[href*="/pics"] img');

        $name = $nameNode->text();

        try {
            $element = $response->filter('span:contains(\'Given name:\')');
            $givenName = str($element->ancestors()->text())
                ->replace($element->text(), '')
                ->trim()
                ->value();
        } catch (Exception $e) {
            $givenName = null;
        }
        try {
            preg_match(
                '~Family name:(.*?)(Alternate names|Birthday|Website|Member Favorites|More)~',
                $response
                    ->filter('span:contains(\'Family name:\')')
                    ->ancestors()
                    ->text(),
                $familyName
            );

            $familyName = str($familyName[1] ?? null)
                ->trim()
                ->value();
        } catch (Exception $e) {
            $familyName = null;
        }
        $japaneseName = implode(', ', array_filter([$familyName, $givenName]));

        try {
            $element = $response->filter('span:contains(\'Alternate names:\')');
            $alternativeNames = explode(',', str_replace($element->text(), '', $element->ancestors()->text()));
        } catch (Exception $e) {
            $alternativeNames = [];
        }

        $websites = [];
        try {
            $element = $response
                ->filter('.people-informantion-more');

            $regex = '/(Twitter|Instagram|Facebook|Blog|Agency):(.*?)\n/';
            $about = str(strip_html($element->html()))
                ->replaceMatches($regex, '')
                ->trim()
                ->value();

            $websites = $element->filter('a:not([href*=\'myanimelist.net\'])')
                ->extract(['href']);
        } catch (Exception $e) {
            $about = null;
        }

        // Marriage and death are only mentioned in free-text; parse conservatively.
        $marriages = [];
        $deceasedDate = null;
        try {
            $infoHTML = $response->filter('.people-informantion-more')
                ->html('');

            if (preg_match_all('#marri(?:age|ed)\b.{0,120}?/people/(\d+)[^>]*>([^<]+)</a>.{0,80}?\b(?:on|in)\s+([A-Z][a-z]+\.?\s+(?:\d{1,2},?\s+)?\d{4})#is', $infoHTML, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $marriages[] = [
                        'id' => $match[1],
                        'name' => str($match[2])->trim()->value(),
                        'date' => str($match[3])->trim()->value(),
                    ];
                }
            }

            if (preg_match('#(?:passed away|died|deceased)\b.{0,120}?\b(?:on|in)\s+([A-Z][a-z]+\.?\s+(?:\d{1,2},?\s+)?\d{4})#is', $infoHTML, $match)) {
                $deceasedDate = str($match[1])->trim()->value();
            }
        } catch (Exception $e) {}

        try {
            $element = $response->filter('span:contains(\'Birthday:\')');
            $birthday = str($element->ancestors()->text())
                ->replace($element->text(), '')
                ->trim()
                ->value();
        } catch (Exception $e) {
            $birthday = null;
        }

        try {
            $website = $response->filter('span:contains(\'Website:\')')
                ->nextAll()
                ->filter('a')
                ->attr('href');

            if ($website !== 'http://') {
                $websites[] = $website;
            }
        } catch (Exception $e) {}

        $animeCharacters = collect($response->filter('table.table-people-character tr')
            ->each(function (Crawler $item) {
                $regex = '/(\d+)\//';
                $id = str($item->filter('td:nth-child(3) a[href*="/character/"]')
                    ->attr('href', ''))
                    ->match($regex)
                    ->value();

                $name = $item->filter('td:nth-child(3) a[href*="/character/"]')
                    ->text('');

                $role = strip_html($item->filter('td:nth-child(3) div:nth-child(2)')
                    ->text(''));

                $animeID = str($item->filter('a[href*="/anime/"]')
                    ->attr('href', ''))
                    ->match($regex)
                    ->value();
                $animeName = $item->filter('a[class*="js-people-title"]')
                    ->text('');

                return [
                    'id' => $id,
                    'name' => $name,
                    'role' => $role,
                    'anime' => [
                        'id' => $animeID,
                        'name' => $animeName,
                    ],
                ];
            }))
            ->filter(fn (array $character) => ! empty($character['id']))
            ->values()
            ->all();

        $animeStaff = collect($response->filter('table.js-table-people-staff tr')
            ->each(function (Crawler $item) {
                $regex = '/(\d+)\//';
                $id = str($item->filter('td:nth-child(2) a[href*="/anime/"]')
                    ->attr('href', ''))
                    ->match($regex)
                    ->value();

                $name = $item->filter('td:nth-child(2) a[href*="/anime/"]')
                    ->text('');

                $roles = $this->parseRoles($item->filter('td:nth-child(2) div:nth-child(2) small')
                    ->text(''));

                return [
                    'id' => $id,
                    'name' => $name,
                    'roles' => $roles,
                ];
            }))
            ->filter(fn (array $staff) => ! empty($staff['id']))
            ->values()
            ->all();

        $mangas = collect($response->filter('table.js-table-people-manga tr')
            ->each(function (Crawler $item) {
                $regex = '/(\d+)\//';
                $id = str($item->filter('td:nth-child(2) a[href*="/manga/"]')
                    ->attr('href', ''))
                    ->match($regex)
                    ->value();

                $name = $item->filter('td:nth-child(2) a[href*="/manga/"]')
                    ->text('');

                $roles = $this->parseRoles($item->filter('td:nth-child(2) div:nth-child(2) small')
                    ->text(''));

                return [
                    'id' => $id,
                    'name' => $name,
                    'roles' => $roles,
                ];
            }))
            ->filter(fn (array $manga) => ! empty($manga['id']))
            ->values()
            ->all();

        yield $this->item(new PersonItem(
            $id,
            $imageURL,
            $name,
            $japaneseName,
            $alternativeNames,
            $about,
            $birthday,
            $websites,
            $animeCharacters,
            $animeStaff,
            $mangas,
            $marriages,
            $deceasedDate
        ));

        // Gallery
        $picturesPageLink = str(config('scraper.domains.mal.people_pictures'))
            ->replace(':x', $id)
            ->value();
        yield ParseResult::request('GET', $picturesPageLink, [$this, 'parsePictures']);
    }

    /**
     * Reads the profile image from the page and rejects MyAnimeList placeholders.
     *
     * @param Response    $response
     * @param null|string $div
     *
     * @return null|string
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
     * Splits a MyAnimeList staff position into its role names.
     *
     * @param string $positions
     *
     * @return array
     */
    private function parseRoles(string $positions): array
    {
        return str(strip_html($positions))
            ->replaceMatches('/\([^)]*\)/', '')
            ->explode(',')
            ->map(fn (string $role) => trim($role))
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }
}
