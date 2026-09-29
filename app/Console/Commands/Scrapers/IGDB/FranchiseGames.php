<?php

namespace App\Console\Commands\Scrapers\IGDB;

use App\Models\Franchise;
use App\Models\Game;
use App\Models\MediaFranchise;
use App\Spiders\IGDB\GameSpider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Laravel\Telescope\Telescope;
use Pulse;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;

class FranchiseGames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:igdb_franchise_games
                            {--dry-run : List the discovered games without scraping them.}
                            {--refresh : Re-read every franchise instead of reusing the cached listing.}
                            {--limit=0 : Only walk the first N franchises.}
                            {--chunk=50 : The number of games scraped per run.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Discovers games that share a franchise with one already in the catalog.';

    /**
     * The GraphQL query listing a franchise's games.
     *
     * @var string
     */
    protected const string QUERY = <<<'GRAPHQL'
        query ($id: ID!) {
          franchise(input: { id: $id }) {
            id name
            games { slug }
          }
        }
        GRAPHQL;

    /**
     * The GraphQL query reading a game's franchises.
     *
     * @var string
     */
    protected const string GAME_QUERY = <<<'GRAPHQL'
        query ($slug: String!) {
          game(input: { slug: $slug }) {
            franchises { id name }
          }
        }
        GRAPHQL;

    /**
     * The courtesy pause between franchises, in microseconds.
     *
     * @var int
     */
    protected const int DELAY_MICROSECONDS = 400000;

    /**
     * The path of the run's cookie jar.
     *
     * @var string|null
     */
    protected ?string $cookieJar = null;

    /**
     * The session CSRF token.
     *
     * @var string|null
     */
    protected ?string $csrfToken = null;

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
        $limit = (int) $this->option('limit');
        $chunk = max(1, (int) $this->option('chunk'));

        if ($isDryRun) {
            $this->warn('Dry run. Nothing will be scraped.');
        }

        if (!$this->startSession()) {
            $this->error('Could not obtain a CSRF token. Adios...');

            return Command::FAILURE;
        }

        $known = Game::withoutGlobalScopes()
            ->whereNotNull('igdb_slug')
            ->pluck('igdb_slug')
            ->flip();

        $this->info($known->count() . ' game(s) already in the catalog.');

        $franchises = Franchise::withoutGlobalScopes()->orderBy('id');

        if ($limit > 0) {
            $franchises->limit($limit);
        }

        $cached = $this->option('refresh') ? [] : $this->loadCache();

        if (!empty($cached)) {
            $this->info(count($cached) . ' franchise(s) read from the cache. Pass --refresh to re-read.');
        }

        $discovered = [];
        $unreachable = [];
        $fetched = 0;

        foreach ($franchises->get(['id', 'igdb_id', 'name']) as $franchise) {
            $igdbID = $franchise->igdb_id ?? $this->resolveIgdbID($franchise);
            $slugs = $igdbID === null ? null : ($cached[$igdbID] ?? null);

            if ($igdbID !== null && $slugs === null) {
                $slugs = $this->franchiseGameSlugs($igdbID);
                $fetched++;

                if ($slugs !== null) {
                    $cached[$igdbID] = $slugs;
                }

                usleep(self::DELAY_MICROSECONDS);
            }

            if ($slugs === null) {
                $unreachable[] = $franchise->name;

                continue;
            }

            $new = array_values(array_filter($slugs, fn (string $slug) => !$known->has($slug) && !isset($discovered[$slug])));

            foreach ($new as $slug) {
                $discovered[$slug] = true;
            }

            if (!empty($new)) {
                $this->line(sprintf('  %-44s %4d new', $franchise->name, count($new)));
            }
        }

        $this->writeCache($cached);
        $this->info($fetched . ' franchise(s) read from IGDB.');

        $slugs = array_keys($discovered);

        $this->newLine();
        $this->info(count($slugs) . ' game(s) discovered through franchises.');

        if (!empty($unreachable)) {
            $this->warn(count($unreachable) . ' franchise(s) could not be read:');
            $this->line('  ' . implode(', ', array_slice($unreachable, 0, 20)));
        }

        if ($isDryRun) {
            foreach ($slugs as $slug) {
                $this->line('  ' . $slug);
            }

            Pulse::startRecording();
            Telescope::startRecording();

            return Command::SUCCESS;
        }

        // Every discovered game still has to pass the anime gate.
        foreach (array_chunk($slugs, $chunk) as $batch) {
            Roach::startSpider(GameSpider::class, new Overrides(), [
                'slugs' => $batch,
                'gatedSlugs' => $batch,
            ]);
        }

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }

    /**
     * Read a franchise's IGDB id off one of its catalogued games.
     *
     * @param Franchise $franchise
     * @return int|null
     */
    protected function resolveIgdbID(Franchise $franchise): ?int
    {
        $memberSlug = Game::withoutGlobalScopes()
            ->whereNotNull('igdb_slug')
            ->whereIn('id', MediaFranchise::withoutGlobalScopes()
                ->where('franchise_id', '=', $franchise->id)
                ->where('model_type', '=', (new Game)->getMorphClass())
                ->select('model_id'))
            ->value('igdb_slug');

        if ($memberSlug === null) {
            return null;
        }

        $data = $this->query(self::GAME_QUERY, $memberSlug);

        foreach ($data['data']['game']['franchises'] ?? [] as $candidate) {
            if (($candidate['name'] ?? null) === $franchise->name && !empty($candidate['id'])) {
                $franchise->update(['igdb_id' => $candidate['id']]);

                return (int) $candidate['id'];
            }
        }

        return null;
    }

    /**
     * The path of the cached franchise listing.
     *
     * @return string
     */
    protected function cachePath(): string
    {
        return base_path('.build/igdb-franchise-slugs.json');
    }

    /**
     * The cached franchise listing.
     *
     * @return array
     */
    protected function loadCache(): array
    {
        $path = $this->cachePath();

        if (!is_file($path)) {
            return [];
        }

        return json_decode(file_get_contents($path), true)['franchises'] ?? [];
    }

    /**
     * Write the franchise listing to the cache.
     *
     * @param array $franchises
     * @return void
     */
    protected function writeCache(array $franchises): void
    {
        $path = $this->cachePath();

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, json_encode([
            'fetched_at' => now()->toIso8601String(),
            'franchises' => $franchises,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Every game slug belonging to a franchise.
     *
     * @param int $igdbID
     * @return array<string>|null
     */
    protected function franchiseGameSlugs(int $igdbID): ?array
    {
        $games = $this->query(self::QUERY, (string) $igdbID)['data']['franchise']['games'] ?? null;

        if (!is_array($games)) {
            return null;
        }

        return collect($games)->pluck('slug')->filter()->unique()->values()->all();
    }

    /**
     * Run a GraphQL query for a single slug.
     *
     * @param string $query
     * @param string $slug
     * @return array
     */
    protected function query(string $query, string $slug): array
    {
        $body = json_encode([
            'query' => $query,
            'variables' => ['slug' => $slug],
        ], JSON_THROW_ON_ERROR);

        $result = Process::timeout((int) config('scraper.curl_impersonate.timeout'))
            ->input($body)
            ->run([
                config('scraper.curl_impersonate.binary'),
                '--impersonate', config('scraper.curl_impersonate.profile'),
                '-s', '--compressed',
                '-c', $this->cookieJar,
                '-b', $this->cookieJar,
                '-X', 'POST',
                '-H', 'content-type: application/json',
                '-H', 'accept: application/json',
                '-H', 'x-csrf-token: ' . $this->csrfToken,
                '-H', 'sec-fetch-mode: cors',
                '-H', 'sec-fetch-dest: empty',
                '-H', 'sec-fetch-site: same-origin',
                '--data-binary', '@-',
                config('scraper.domains.igdb.gql'),
            ]);

        if ($result->failed()) {
            return [];
        }

        return json_decode($result->output(), true) ?: [];
    }

    /**
     * Open a session and read its CSRF token.
     *
     * @return bool
     */
    protected function startSession(): bool
    {
        $this->cookieJar = tempnam(sys_get_temp_dir(), 'igdb_cookies_');

        Process::timeout((int) config('scraper.curl_impersonate.timeout'))->run([
            config('scraper.curl_impersonate.binary'),
            '--impersonate', config('scraper.curl_impersonate.profile'),
            '-s', '-o', '/dev/null',
            '-c', $this->cookieJar,
            '-b', $this->cookieJar,
            config('scraper.domains.igdb.base'),
        ]);

        foreach (file($this->cookieJar, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
            $columns = explode("\t", $line);

            if (count($columns) >= 7 && $columns[5] === 'csrf') {
                $this->csrfToken = $columns[6];

                return true;
            }
        }

        return false;
    }
}
