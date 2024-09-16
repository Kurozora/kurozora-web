<?php

namespace App\Spiders\MAL;

use App\Processors\MAL\ProducerProcessor;
use App\Spiders\MAL\Models\ProducerItem;
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

class ProducerSpider extends BasicSpider
{
    /**
     * The list of start urls.
     *
     * @var list<string> $startUrls
     */
    public array $startUrls = [
        //
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
        ]
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
        ProducerProcessor::class
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
            logger()->error('Producer: ' . $id . ';status:' . $response->getStatus());

            return $this->item([]);
        }

        logger()->channel('stderr')->info('🕷 [MAL_ID:PRODUCER:' . $id . '] Parsing response');
        $name = $response->filter('h1.title-name')
            ->text();

        $imageURL = $this->cleanImageURL($response, '.logo img[data-src*=\'company_logos\']');

        try {
            $element = $response->filter('span:contains(\'Japanese:\')');
            $japaneseName = str($element->ancestors()->text())
                ->replace($element->text(), '')
                ->trim()
                ->value();
        } catch (Exception $e) {
            $japaneseName = null;
        }

        try {
            $element = $response->filter('span:contains(\'Synonyms:\')');
            $synonymTitles = str($element->ancestors()->text())
                ->replace($element->text(), '')
                ->trim()
                ->value();
            $synonymTitles = collect(explode(', ', $synonymTitles))
                ->transform(function ($synonymTitle) {
                    return $this->cleanHTML($synonymTitle);
                })
                ->filter(function ($synonymTitle) {
                    return !empty($synonymTitle);
                })
                ->toArray();
        } catch (Exception $e) {
            $synonymTitles = [];
        }

        try {
            $about = null;
            $element = $response->filter('.content-left div[class=\'spaceit_pad\'] span:not([class=\'dark_text\'])');

            if ($element->count()) {
                $about = $this->cleanHTML($element->text());
            }
        } catch (Exception $e) {
            //
        }

        try {
            $element = $response->filter('span:contains(\'Established:\')');
            $established = str($element->ancestors()->text())
                ->replace($element->text(), '')
                ->trim()
                ->value();
        } catch (Exception $e) {
            $established = null;
        }

        try {
            $element = $response->filter('span:contains(\'Dissolved:\')');
            $dissolved = str($element->ancestors()->text())
                ->replace($element->text(), '')
                ->trim()
                ->value();
        } catch (Exception $e) {
            $dissolved = null;
        }

        try {
            $socialLinks = [];
            $availableAtLinks = $response->filter('.user-profile-sns span a');

            if ($availableAtLinks->count()) {
                $socialLinks = $availableAtLinks
                    ->each(function (Crawler $item) {
                        return $this->cleanHTML($item->attr('href'));
                    });
            }
        } catch (Exception $e) {
            //
        }

        try {
            $websiteLinks = [];
            $resourcesLinks = $response->filter('.pb16 span a');

            if ($resourcesLinks->count()) {
                $websiteLinks = $resourcesLinks
                    ->each(function (Crawler $item) {
                        return $this->cleanHTML($item->attr('href'));
                    });
            }
        } catch (Exception $e) {
            //
        }

        yield $this->item(new ProducerItem(
            $id,
            $imageURL,
            $name,
            $japaneseName,
            $synonymTitles,
            $about,
            $established,
            $dissolved,
            $socialLinks,
            $websiteLinks
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
        $match = $imageURL->contains(['questionmark', 'qm_50', 'na.gif', 'company_no_picture']);
        if ($match) {
            return null;
        }

        // Get base image url
        $cleanImageURL = $imageURL->replace('v.jpg', '.jpg');
        $cleanImageURL = $cleanImageURL->replace('t.jpg', '.jpg');
        $cleanImageURL = $cleanImageURL->replace('_thumb.jpg', '.jpg');
        $cleanImageURL = $cleanImageURL->replace('userimages/thumbs', 'userimages');
        $cleanImageURL = $cleanImageURL->replace('_600x600_i?', '?');
        $cleanImageURL = $cleanImageURL->value();

        // Remove queries and bs
        $regex = '/r\/\d{1,3}x\d{1,3}\//';
        $cleanImageURL = preg_replace($regex, '', $cleanImageURL);
        $regex = '/\?.+/';

        // Return clean url
        return preg_replace($regex, '', $cleanImageURL);
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
