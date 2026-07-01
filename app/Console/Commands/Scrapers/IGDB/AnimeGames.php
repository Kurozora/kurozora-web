<?php

namespace App\Console\Commands\Scrapers\IGDB;

use App\Models\Game as GameModel;
use App\Models\Platform;
use App\Models\Studio;
use App\Spiders\IGDB\GameSpider;
use App\Spiders\IGDB\Http\CurlImpersonateGqlClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Laravel\Telescope\Telescope;
use Pulse;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;

class AnimeGames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:igdb_anime_games
                            {--cache : Persist each facet\'s discovered slugs and only scrape newly-added games on later runs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Discover and scrape anime-related games from the IGDB anime facets.';

    /**
     * The keyword facets whose members are anime-precise and admitted outright.
     *
     * @var array<string>
     */
    protected const array CLEAN_CATEGORIES = [
        'anime',
        'manga',
        'bishoujo',
        'magical-girl',
        'harem',
        'yuri',
        'yaoi',
        'josei',
        'galge',
        'kawaii',
    ];

    /**
     * The keyword facets whose members must clear the anime gate before persisting.
     *
     * @var array<string>
     */
    protected const array GATED_CATEGORIES = [
        'eroge',
        'otaku',
        'mecha',
        'dating-sim',
    ];

    /**
     * The genre facets whose members must clear the anime gate before persisting.
     *
     * @var array<string>
     */
    protected const array GATED_GENRES = [
        'visual-novel',
    ];

    /**
     * The facet sort that surfaces newly-added games first.
     *
     * @var string
     */
    protected const string NEWLY_ADDED_SORT = 'releasedate=desc';

    /**
     * The upper bound on facet pages, guarding against an unterminated crawl.
     *
     * @var int
     */
    protected const int MAXIMUM_PAGES = 2000;

    /**
     * The number of attempts per page before giving up on a throttled response.
     *
     * @var int
     */
    protected const int FETCH_ATTEMPTS = 4;

    /**
     * The courtesy pause between facet pages, in microseconds.
     *
     * @var int
     */
    protected const int PAGE_DELAY_MICROSECONDS = 300000;

    /**
     * The per-attempt backoff applied after a throttled response, in microseconds.
     *
     * @var int
     */
    protected const int RETRY_DELAY_MICROSECONDS = 1000000;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $cache = (bool) $this->option('cache');

        Pulse::stopRecording();
        Telescope::stopRecording();
        GameModel::disableSearchSyncing();
        Platform::disableSearchSyncing();
        Studio::disableSearchSyncing();

        config(['roach.client' => CurlImpersonateGqlClient::class]);

        foreach (self::CLEAN_CATEGORIES as $facet) {
            $this->scrapeFacet(config('scraper.domains.igdb.category'), $facet, false, $cache);
        }

        foreach (self::GATED_CATEGORIES as $facet) {
            $this->scrapeFacet(config('scraper.domains.igdb.category'), $facet, true, $cache);
        }

        foreach (self::GATED_GENRES as $facet) {
            $this->scrapeFacet(config('scraper.domains.igdb.genre'), $facet, true, $cache);
        }

        GameModel::enableSearchSyncing();
        Platform::enableSearchSyncing();
        Studio::enableSearchSyncing();
        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }

    /**
     * Discover and scrape a single facet's newly-added games.
     *
     * @param string $urlTemplate
     * @param string $facet
     * @param bool   $gated
     * @param bool   $cache
     *
     * @return void
     */
    protected function scrapeFacet(string $urlTemplate, string $facet, bool $gated, bool $cache): void
    {
        [$known, $completed] = $cache ? $this->loadCache($facet) : [[], false];

        [$new, $reachedEnd] = $this->discoverNewSlugs($urlTemplate, $facet, array_flip($known), $completed);

        $this->info('[' . $facet . '] ' . count($new) . ' new game(s) to scrape.');

        if (!empty($new)) {
            if ($cache) {
                if (!is_file($this->cachePath($facet))) {
                    $this->writeCache($facet, $known, $completed);
                }

                config(['scraper.igdb.cache_path' => $this->cachePath($facet)]);
            }

            Roach::startSpider(GameSpider::class, new Overrides(), [
                'slugs' => $new,
                'gatedSlugs' => $gated ? $new : [],
            ]);
        }

        if ($cache && $reachedEnd) {
            $this->markCompleted($facet);
        }
    }

    /**
     * Page a facet in newly-added order, collecting slugs not already known.
     *
     * @param string             $urlTemplate
     * @param string             $facet
     * @param array<string, int> $known
     * @param bool               $completed
     *
     * @return array{0: array<string>, 1: bool}
     */
    protected function discoverNewSlugs(string $urlTemplate, string $facet, array $known, bool $completed): array
    {
        $new = [];
        $reachedEnd = false;

        for ($page = 1; $page <= self::MAXIMUM_PAGES; $page++) {
            $pageSlugs = $this->fetchFacetSlugs($urlTemplate, $facet, $page);

            if ($pageSlugs === null) {
                $this->warn('[' . $facet . '] page ' . $page . ' could not be fetched; stopping short of the end.');
                break;
            }

            if (empty($pageSlugs)) {
                $reachedEnd = true;
                break;
            }

            $pageHasNew = false;

            foreach ($pageSlugs as $slug) {
                if (isset($known[$slug])) {
                    continue;
                }

                $new[$slug] = true;
                $pageHasNew = true;
            }

            if ($completed && !$pageHasNew) {
                $reachedEnd = true;
                break;
            }

            usleep(self::PAGE_DELAY_MICROSECONDS);
        }

        return [array_keys($new), $reachedEnd];
    }

    /**
     * Fetch the game slugs on a single facet page, newest additions first.
     *
     * @param string $urlTemplate
     * @param string $facet
     * @param int    $page
     *
     * @return array<string>|null
     */
    protected function fetchFacetSlugs(string $urlTemplate, string $facet, int $page): ?array
    {
        $query = [self::NEWLY_ADDED_SORT, 'page=' . $page];

        $url = str($urlTemplate)
            ->replace(':x', $facet)
            ->append('?' . implode('&', $query))
            ->value();

        for ($attempt = 0; $attempt < self::FETCH_ATTEMPTS; $attempt++) {
            if ($attempt > 0) {
                usleep($attempt * self::RETRY_DELAY_MICROSECONDS);
            }

            $result = Process::timeout((int) config('scraper.curl_impersonate.timeout'))->run([
                config('scraper.curl_impersonate.binary'),
                '--impersonate', config('scraper.curl_impersonate.profile'),
                '-sL', '--compressed',
                '-H', 'x-requested-with: XMLHttpRequest',
                '-H', 'accept: application/json',
                $url,
            ]);

            if ($result->failed()) {
                continue;
            }

            $slugs = $this->parseFacetSlugs($result->output());

            if ($slugs !== null) {
                return $slugs;
            }
        }

        return null;
    }

    /**
     * Extract the game slugs from a facet response.
     *
     * @param string $body
     *
     * @return array<string>|null
     */
    protected function parseFacetSlugs(string $body): ?array
    {
        $data = json_decode($body, true);

        if (is_array($data) && array_key_exists('games', $data)) {
            return collect($data['games'])->pluck('slug')->filter()->values()->toArray();
        }

        // Fall back to the embedded payload when a non-XHR response is served.
        if (preg_match_all('/<script[^>]*>(.*?)<\/script>/s', $body, $matches)) {
            foreach ($matches[1] as $script) {
                $script = trim(html_entity_decode($script));

                if (!str_starts_with($script, '{"games"')) {
                    continue;
                }

                $decoded = json_decode($script, true);

                return collect($decoded['games'] ?? [])->pluck('slug')->filter()->values()->toArray();
            }
        }

        return null;
    }

    /**
     * The cache path for a facet.
     *
     * @param string $facet
     *
     * @return string
     */
    protected function cachePath(string $facet): string
    {
        return base_path('.build/igdb-' . $facet . '-slugs.json');
    }

    /**
     * Load a facet's known slugs and whether it has been crawled end to end.
     *
     * @param string $facet
     *
     * @return array{0: array<string>, 1: bool}
     */
    protected function loadCache(string $facet): array
    {
        $path = $this->cachePath($facet);

        if (!is_file($path)) {
            return [[], false];
        }

        $state = json_decode(file_get_contents($path), true) ?: [];
        $slugs = array_values(array_filter($state['slugs'] ?? []));
        $completed = $state['completed'] ?? !empty($slugs);

        return [$slugs, $completed];
    }

    /**
     * Write a facet's cache.
     *
     * @param string        $facet
     * @param array<string> $slugs
     * @param bool          $completed
     *
     * @return void
     */
    protected function writeCache(string $facet, array $slugs, bool $completed): void
    {
        $this->putJson($this->cachePath($facet), [
            'completed' => $completed,
            'slugs' => array_values(array_unique(array_filter($slugs))),
        ]);
    }

    /**
     * Mark a facet's cache as fully crawled while preserving slugs the processor appended.
     *
     * @param string $facet
     *
     * @return void
     */
    protected function markCompleted(string $facet): void
    {
        $path = $this->cachePath($facet);
        $state = is_file($path) ? (json_decode(file_get_contents($path), true) ?: []) : [];

        $this->putJson($path, [
            'completed' => true,
            'slugs' => array_values(array_unique(array_filter($state['slugs'] ?? []))),
        ]);
    }

    /**
     * Write a JSON payload.
     *
     * @param string $path
     * @param array  $data
     *
     * @return void
     */
    protected function putJson(string $path, array $data): void
    {
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
