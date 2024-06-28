<?php

namespace App\Spiders\MAL;

use App\Processors\MAL\PersonProcessor;
use App\Spiders\MAL\Models\PersonItem;
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
        PersonProcessor::class
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
            logger()->error('Person: ' . $id);
            if ($response->getStatus() != 404) {
                logger()->warning('Person: ' . $response->getStatus());
            }
            return $this->item([]);
        }

        logger()->channel('stderr')->info('🕷 [MAL_ID:PERSON:' . $id . '] Parsing response');

        $imageURL = $response->filter('meta[property="og:image"]')
            ->attr('content');

        $name = $response->filter('h1.title-name')
            ->text();

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
            $about = str($this->cleanHTML($element->html()))
                ->replaceMatches($regex, '')
                ->trim()
                ->value();

            $websites = $element->filter('a:not([href*=\'myanimelist.net\'])')
                ->extract(['href']);
        } catch (Exception $e) {
            $about = null;
        }

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

        $animeCharacters = $response->filter('table.table-people-character tr')
            ->each(function (Crawler $item) {
                $regex = '/(\d+)\//';
                $id = str($item->filter('td:nth-child(3) a[href*="/character/"]')
                    ->attr('href'))
                    ->match($regex)
                    ->value();

                $name = $item->filter('td:nth-child(3) a[href*="/character/"]')
                    ->text();

                $role = $this->cleanHTML($item->filter('td:nth-child(3) div:nth-child(2)')
                    ->text());

                return [
                    'id' => $id,
                    'name' => $name,
                    'role' => $role
                ];
            });
dd($animeCharacters);
        $animeStaff = $response->filter('table.js-table-people-staff tr')
            ->each(function (Crawler $item) {
                $regex = '/(\d+)\//';
                $id = str($item->filter('td:nth-child(2) a[href*="/anime/"]')
                    ->attr('href'))
                    ->match($regex)
                    ->value();

                $name = $item->filter('td:nth-child(2) a[href*="/anime/"]')
                    ->text();

                $regex = '/\([^)]*\)/';
                $roles = str($this->cleanHTML($item->filter('td:nth-child(2) div:nth-child(2) small')
                    ->text()))
                    ->explode(', ')
                    ->transform(function (string $string) use ($regex) {
                        return str($string)
                            ->replaceMatches($regex, '')
                            ->trim()
                            ->value();
                    })
                    ->toArray();

                return [
                    'id' => $id,
                    'name' => $name,
                    'roles' => $roles
                ];
            });

        $mangas = $response->filter('table.js-table-people-manga tr')
            ->each(function (Crawler $item) {
                $regex = '/(\d+)\//';
                $id = str($item->filter('td:nth-child(2) a[href*="/manga/"]')
                    ->attr('href'))
                    ->match($regex)
                    ->value();

                $name = $item->filter('td:nth-child(2) a[href*="/manga/"]')
                    ->text();

                $roles = str($this->cleanHTML($item->filter('td:nth-child(2) div:nth-child(2) small')
                    ->text()))
                    ->explode(', ')
                    ->toArray();

                return [
                    'id' => $id,
                    'name' => $name,
                    'roles' => $roles
                ];
            });

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
            $mangas
        ));
    }

    /**
     * Cleans the given HTML string.
     *
     * @param string $string
     *
     * @return string
     */
    public static function cleanHTML(string $string): string
    {
        // Convert breaks to new line
        $string = str_replace(
            ['<br>', '<br />', '<br/>', '<br >'],
            "\\n",
            $string
        );

        // Convert nbsp to space
        $string = str_replace("\xc2\xa0", ' ', $string);

        // Remove control characters
        $string = preg_replace('~[[:cntrl:]]~', '', $string);

        // Strip any leftover tags
        $string = strip_tags($string);

        // Remove any newlines at the end
        $string = str_replace('\\n', "\n", $string);

        // Trim and return
        return trim($string);
    }
}
