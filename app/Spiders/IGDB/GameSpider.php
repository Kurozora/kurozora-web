<?php

namespace App\Spiders\IGDB;

use App\Processors\IGDB\GameProcessor;
use App\Spiders\IGDB\Models\GameItem;
use App\Spiders\MAL\Middleware\BackoffMiddleware;
use App\Spiders\MAL\Middleware\RateLimitMiddleware;
use Generator;
use RoachPHP\Downloader\DownloaderMiddlewareInterface;
use RoachPHP\Downloader\Middleware\RequestMiddlewareInterface;
use RoachPHP\Downloader\Middleware\ResponseMiddlewareInterface;
use RoachPHP\Extensions\ExtensionInterface;
use RoachPHP\Extensions\LoggerExtension;
use RoachPHP\Extensions\StatsCollectorExtension;
use RoachPHP\Http\Request;
use RoachPHP\Http\Response;
use RoachPHP\ItemPipeline\Processors\ItemProcessorInterface;
use RoachPHP\Spider\BasicSpider;
use RoachPHP\Spider\ParseResult;
use RoachPHP\Spider\SpiderMiddlewareInterface;

class GameSpider extends BasicSpider
{
    /**
     * The GraphQL query for a single game's detail page.
     *
     * @var string
     */
    protected const string QUERY = <<<'GRAPHQL'
        query ($slug: String!) {
          game(input: { slug: $slug }) {
            id slug name summary storyline category categoryName firstReleaseDate gameRatingBreakdown
            coverSrc(imageType: "cover_big")
            rating { userRating userRatingsCount criticRating criticRatingsCount }
            timeToBeat { hastily normally completely count }
            genres { id slug name }
            themes { id slug name }
            gameModes { id slug name }
            playerPerspectives { id slug name }
            gameEngines { id slug name }
            franchises { id slug name }
            developers { id slug name description startDate country }
            publishers { id slug name description startDate }
            supportingDevelopers { id slug name description startDate }
            portingDevelopers { id slug name description startDate }
            languageSupports { language { id name nativeName locale } languageSupportType { id name } }
            releases { releaseDate region platform { id slug name category generation alternativeName } releaseDateStatus { name } }
            websites { url websiteType { name } }
            videos { id name videoId }
            alternativeNames { name }
            localizations { name region { identifier } }
            gameRatings { id rating category }
            versionParent { id slug name }
            dlcs { id slug name }
            expansions { id slug name }
            expandedGames { id slug name }
            standalones { id slug name }
            versions { id slug name }
            seasons { id slug name }
            episodes { id slug name }
            remake { id slug name }
            remasters { id slug name }
            ports { id slug name }
            forks { id slug name }
          }
        }
        GRAPHQL;

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
        BackoffMiddleware::class,
        [
            RateLimitMiddleware::class,
            [
                'key' => 'scraper:igdb',
                'max_attempts' => 20,
                'decay_seconds' => 60,
                'max_wait_seconds' => 300,
            ],
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
        GameProcessor::class,
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
    public int $concurrency = 1;

    /**
     * The delay (in seconds) between requests.
     *
     * @var int $requestDelay
     */
    public int $requestDelay = 1;

    /**
     * Build a GraphQL request for each slug passed in the run context.
     *
     * @return array<array-key, Request>
     */
    protected function initialRequests(): array
    {
        $slugs = $this->context['slugs'] ?? [];
        $gatedSlugs = array_flip($this->context['gatedSlugs'] ?? []);
        $endpoint = config('scraper.domains.igdb.gql');

        return array_map(function (string $slug) use ($endpoint, $gatedSlugs) {
            return new Request('POST', $endpoint, [$this, 'parse'], [
                'json' => [
                    'query' => self::QUERY,
                    'variables' => ['slug' => $slug],
                ],
            ])->withMeta('slug', $slug)->withMeta('gated', isset($gatedSlugs[$slug]));
        }, $slugs);
    }

    /**
     * Parse the GraphQL response into a game item.
     *
     * @param Response $response
     *
     * @return Generator<ParseResult>
     */
    public function parse(Response $response): Generator
    {
        $slug = $response->getRequest()->getMeta('slug');

        if ($response->getStatus() >= 400) {
            logger()->error('IGDB: ' . $slug . ';status:' . $response->getStatus());
            return;
        }

        $payload = json_decode($response->getBody(), true);
        $game = $payload['data']['game'] ?? null;

        if (empty($game)) {
            $errors = $payload['errors'] ?? null;
            logger()->error('IGDB: ' . $slug . ';missing-game', ['errors' => $errors]);
            return;
        }

        $game['coverSrc'] = $this->cleanCoverSrc($game['coverSrc'] ?? null);

        logger()->channel('stderr')->debug('🕹 [IGDB:' . $slug . '] Parsed game');

        yield $this->item(new GameItem($slug, $game, (bool) $response->getRequest()->getMeta('gated')));
    }

    /**
     * Clean the cover source, rejecting "no cover" placeholders.
     *
     * @param string|null $coverSrc
     *
     * @return string|null
     */
    private function cleanCoverSrc(?string $coverSrc): ?string
    {
        if (empty($coverSrc)) {
            return null;
        }

        if (str($coverSrc)->lower()->contains(['no_cover', 'nocover'])) {
            return null;
        }

        return $coverSrc;
    }
}
