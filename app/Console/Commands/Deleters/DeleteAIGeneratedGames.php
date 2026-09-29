<?php

namespace App\Console\Commands\Deleters;

use App\Models\Game;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Process;
use Laravel\Telescope\Telescope;
use Pulse;

class DeleteAIGeneratedGames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:ai_generated_games
                            {--dry-run : Report what would be deleted without deleting it.}
                            {--refresh : Re-fetch the facet listing instead of reusing the cached one.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete games that IGDB tags as built with AI-generated assets.';

    /**
     * The IGDB keyword facets that list games built with AI-generated assets.
     *
     * @var string[]
     */
    protected const array AI_FACETS = [
        'ai-generated',
        'ai-generated-artwork',
        'ai-generated-text',
        'ai-generated-music',
    ];

    /**
     * The number of games loaded per chunk.
     *
     * @var int
     */
    protected const int CHUNK_SIZE = 200;

    /**
     * The upper bound on facet pages.
     *
     * @var int
     */
    protected const int MAXIMUM_PAGES = 500;

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
     * The path of the run's cookie jar.
     *
     * @var string|null
     */
    protected ?string $cookieJar = null;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Pulse::stopRecording();
        Telescope::stopRecording();

        $isDryRun = (bool) $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('Dry run. Nothing will be deleted.');
        }

        $cached = $this->option('refresh') ? null : $this->loadCache();

        if ($cached !== null) {
            [$facetsBySlug, $truncated, $fetchedAt] = $cached;
            $this->info('Reusing the facet listing fetched ' . $fetchedAt->diffForHumans() . '. Pass --refresh to re-fetch.');
        } else {
            $facetsBySlug = [];
            $truncated = false;

            foreach (self::AI_FACETS as $facet) {
                [$slugs, $reachedEnd] = $this->facetSlugs($facet);
                $truncated = $truncated || !$reachedEnd;

                foreach ($slugs as $slug) {
                    $facetsBySlug[$slug][] = $facet;
                }

                $this->info('[' . $facet . '] ' . count($slugs) . ' game(s)' . ($reachedEnd ? '.' : ', incomplete.'));
            }

            $this->writeCache($facetsBySlug, $truncated);
        }

        if (empty($facetsBySlug)) {
            $this->error('No AI-generated games listed by IGDB. Adios...');

            return Command::FAILURE;
        }

        $this->info(count($facetsBySlug) . ' AI-generated game(s) listed by IGDB.');

        if ($truncated) {
            $this->warn('At least one facet could not be read to the end. Re-run before deleting.');
        }

        $found = [];

        Game::withoutGlobalScopes()
            ->whereIn('igdb_slug', array_keys($facetsBySlug))
            ->orderBy('id')
            ->chunkById(self::CHUNK_SIZE, function (Collection $games) use (&$found, $facetsBySlug, $isDryRun) {
                foreach ($games as $game) {
                    $found[] = [$game, $facetsBySlug[$game->igdb_slug] ?? []];

                    if (!$isDryRun) {
                        logger()->channel('stderr')->info('🗑 [IGDB:' . $game->igdb_slug . '] AI-generated, removing game ' . $game->id . '.');
                        $game->delete();
                    }
                }
            });

        $this->newLine();

        foreach ($found as [$game, $facets]) {
            $this->line(($isDryRun ? '  WOULD DELETE  ' : '  DELETED  ') . $game->original_title . '  [' . $game->igdb_slug . ']  (' . implode(', ', $facets) . ')');
        }

        $this->newLine();
        $this->info(($isDryRun ? 'Would delete: ' : 'Deleted: ') . count($found) . '.');

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }

    /**
     * The cookie jar shared across the run.
     *
     * @return string
     */
    protected function cookieJar(): string
    {
        return $this->cookieJar ??= tempnam(sys_get_temp_dir(), 'igdb_cookies_');
    }

    /**
     * The path of the cached facet listing.
     *
     * @return string
     */
    protected function cachePath(): string
    {
        return base_path('.build/igdb-ai-slugs.json');
    }

    /**
     * The cached facet listing with its truncation flag and fetch time.
     *
     * @return array{0: array<string, array<string>>, 1: bool, 2: Carbon}|null
     */
    protected function loadCache(): ?array
    {
        $path = $this->cachePath();

        if (!is_file($path)) {
            return null;
        }

        $state = json_decode(file_get_contents($path), true) ?: [];

        if (empty($state['facets'])) {
            return null;
        }

        return [
            $state['facets'],
            (bool) ($state['truncated'] ?? false),
            Carbon::parse($state['fetched_at'] ?? 'now'),
        ];
    }

    /**
     * Write the facet listing to the cache.
     *
     * @param array<string, array<string>> $facetsBySlug
     * @param bool                         $truncated
     *
     * @return void
     */
    protected function writeCache(array $facetsBySlug, bool $truncated): void
    {
        $path = $this->cachePath();

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, json_encode([
            'fetched_at' => now()->toIso8601String(),
            'truncated' => $truncated,
            'facets' => $facetsBySlug,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Every game slug in a facet, with whether the facet was read to its end.
     *
     * @param string $facet
     *
     * @return array{0: array<string>, 1: bool}
     */
    protected function facetSlugs(string $facet): array
    {
        $slugs = [];

        for ($page = 1; $page <= self::MAXIMUM_PAGES; $page++) {
            $pageSlugs = $this->fetchPage($facet, $page);

            if ($pageSlugs === null) {
                $this->warn('[' . $facet . '] page ' . $page . ' could not be fetched.');

                return [array_values(array_unique($slugs)), false];
            }

            if (empty($pageSlugs)) {
                return [array_values(array_unique($slugs)), true];
            }

            $slugs = array_merge($slugs, $pageSlugs);
            usleep(self::PAGE_DELAY_MICROSECONDS);
        }

        return [array_values(array_unique($slugs)), false];
    }

    /**
     * The game slugs on a single facet page.
     *
     * @param string $facet
     * @param int    $page
     *
     * @return array<string>|null
     */
    protected function fetchPage(string $facet, int $page): ?array
    {
        $url = str(config('scraper.domains.igdb.category'))
            ->replace(':x', $facet)
            ->append('?releasedate=desc&page=' . $page)
            ->value();

        for ($attempt = 0; $attempt < self::FETCH_ATTEMPTS; $attempt++) {
            if ($attempt > 0) {
                usleep($attempt * self::RETRY_DELAY_MICROSECONDS);
            }

            $result = Process::timeout((int) config('scraper.curl_impersonate.timeout'))->run([
                config('scraper.curl_impersonate.binary'),
                '--impersonate', config('scraper.curl_impersonate.profile'),
                '-sL', '--compressed',
                '-c', $this->cookieJar(),
                '-b', $this->cookieJar(),
                '-H', 'x-requested-with: XMLHttpRequest',
                '-H', 'accept: application/json',
                $url,
            ]);

            if ($result->failed()) {
                continue;
            }

            $data = json_decode($result->output(), true);

            if (is_array($data) && array_key_exists('games', $data)) {
                return collect($data['games'])->pluck('slug')->filter()->values()->all();
            }
        }

        return null;
    }
}
