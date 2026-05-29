<?php

namespace App\Console\Commands\Generators;

use App\Models\Anime;
use App\Models\Character;
use App\Models\Episode;
use App\Models\ExploreCategory;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Manga;
use App\Models\Person;
use App\Models\Season;
use App\Models\SitemapShard;
use App\Models\Song;
use App\Models\Studio;
use App\Models\Theme;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Storage;
use Laravel\Telescope\Telescope;
use Pulse;

class GenerateSitemap extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'generate:sitemaps {--force : Rebuild every shard regardless of stored state}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sitemaps and upload them to the CDN bucket.';

    /**
     * Rows per shard.
     */
    private const int SHARD_SIZE = 20000;

    /**
     * S3 prefix for sitemap files.
     */
    private const string S3_PREFIX = 'sitemaps';

    /**
     * Change frequency reported in every URL.
     */
    private const string CHANGE_FREQUENCY = 'weekly';

    /**
     * Default PUT options for sitemap files.
     *
     * @var array<string, string>
     */
    private const array S3_PUT_OPTIONS = [
        'ContentType'  => 'application/xml',
        'CacheControl' => 'public, max-age=3600',
    ];

    /**
     * Execute the console command.
     *
     * @return int
     * @throws ConnectionException
     */
    public function handle(): int
    {
        Pulse::stopRecording();
        Telescope::stopRecording();

        $sources = $this->sourceDefinitions();

        $force = (bool) $this->option('force');
        $indexEntries = [];

        foreach ($sources as $modelClass => $config) {
            $indexEntries = array_merge(
                $indexEntries,
                $this->processSource($modelClass, $config, $force),
            );
        }

        $indexEntries[] = $this->generateStaticPagesSitemap();

        $this->uploadSitemapIndex($indexEntries);

        Pulse::startRecording();
        Telescope::startRecording();

        $this->info('- Done -');

        return self::SUCCESS;
    }

    /**
     * Source definitions for every sitemap stream.
     *
     * @return array<class-string<Model>, array{streams: array<string, string>, select: array<int, string>, soft_deletes: bool}>
     */
    private function sourceDefinitions(): array
    {
        return [
            Anime::class => [
                'streams' => [
                    Anime::TABLE_NAME => 'anime.details',
                    'anime_cast'      => 'anime.cast',
                    'anime_songs'     => 'anime.songs',
                    'anime_staff'     => 'anime.staff',
                    'anime_studios'   => 'anime.studios',
                ],
                'select'       => ['id', 'slug', 'updated_at'],
                'soft_deletes' => true,
            ],
            Manga::class => [
                'streams' => [
                    Manga::TABLE_NAME => 'manga.details',
                    'manga_cast'      => 'manga.cast',
                    'manga_staff'     => 'manga.staff',
                    'manga_studios'   => 'manga.studios',
                ],
                'select'       => ['id', 'slug', 'updated_at'],
                'soft_deletes' => true,
            ],
            Game::class => [
                'streams' => [
                    Game::TABLE_NAME => 'games.details',
                    'game_cast'      => 'games.cast',
                    'game_songs'     => 'games.songs',
                    'game_staff'     => 'games.staff',
                    'game_studios'   => 'games.studios',
                ],
                'select'       => ['id', 'slug', 'updated_at'],
                'soft_deletes' => true,
            ],
            Episode::class => [
                'streams'      => [Episode::TABLE_NAME => 'episodes.details'],
                'select'       => ['id', 'public_id', 'updated_at'],
                'soft_deletes' => true,
            ],
            Season::class => [
                'streams'      => [Season::TABLE_NAME => 'seasons.episodes'],
                'select'       => ['id', 'public_id', 'updated_at'],
                'soft_deletes' => true,
            ],
            Song::class => [
                'streams'      => [Song::TABLE_NAME => 'songs.details'],
                'select'       => ['id', 'updated_at'],
                'soft_deletes' => true,
            ],
            Character::class => [
                'streams'      => [Character::TABLE_NAME => 'characters.details'],
                'select'       => ['id', 'slug', 'updated_at'],
                'soft_deletes' => true,
            ],
            Genre::class => [
                'streams'      => [Genre::TABLE_NAME => 'genres.details'],
                'select'       => ['id', 'slug', 'updated_at'],
                'soft_deletes' => true,
            ],
            Theme::class => [
                'streams'      => [Theme::TABLE_NAME => 'themes.details'],
                'select'       => ['id', 'slug', 'updated_at'],
                'soft_deletes' => true,
            ],
            Person::class => [
                'streams'      => [Person::TABLE_NAME => 'people.details'],
                'select'       => ['id', 'slug', 'updated_at'],
                'soft_deletes' => true,
            ],
            Studio::class => [
                'streams'      => [Studio::TABLE_NAME => 'studios.details'],
                'select'       => ['id', 'slug', 'updated_at'],
                'soft_deletes' => true,
            ],
            User::class => [
                'streams'      => [User::TABLE_NAME => 'profile.details'],
                'select'       => ['id', 'slug', 'updated_at'],
                'soft_deletes' => false,
            ],
            ExploreCategory::class => [
                'streams'      => [ExploreCategory::TABLE_NAME => 'explore.details'],
                'select'       => ['id', 'slug', 'updated_at'],
                'soft_deletes' => true,
            ],
        ];
    }

    /**
     * Generate every shard for a source.
     *
     * @param class-string<Model>                                                                   $modelClass
     * @param array{streams: array<string, string>, select: array<int, string>, soft_deletes: bool} $config
     * @param bool                                                                                  $force
     *
     * @return array<int, array{loc: string, lastmod: CarbonInterface}>
     * @throws ConnectionException
     */
    private function processSource(string $modelClass, array $config, bool $force): array
    {
        $tableName = $modelClass::TABLE_NAME;

        $bounds = $modelClass::query()
            ->withoutGlobalScopes()
            ->selectRaw('COALESCE(MIN(id), 0) AS min_id, COALESCE(MAX(id), 0) AS max_id')
            ->first();

        $maxId = (int) ($bounds->max_id ?? 0);

        if ($maxId === 0) {
            return [];
        }

        $shardCount = (int) ceil($maxId / self::SHARD_SIZE);
        $this->info(sprintf('- %s: %d shard(s)', $tableName, $shardCount));

        $entries = [];

        for ($shardIndex = 1; $shardIndex <= $shardCount; $shardIndex++) {
            $rangeStart = ($shardIndex - 1) * self::SHARD_SIZE + 1;
            $rangeEnd   = $shardIndex * self::SHARD_SIZE;

            $gate = $this->runGateQuery($modelClass, $config, $rangeStart, $rangeEnd);
            $rowCount   = (int) $gate->row_count;
            $maxUpdated = $gate->max_updated_at !== null ? Carbon::parse($gate->max_updated_at) : null;

            $stored = SitemapShard::query()
                ->where('source_table', $tableName)
                ->where('shard_index', $shardIndex)
                ->first();

            if (!$force && $this->shardUnchanged($stored, $rowCount, $maxUpdated)) {
                $shardLastmod = $stored->max_updated_at ?? $stored->generated_at ?? Carbon::now();

                foreach (array_keys($config['streams']) as $streamKey) {
                    $entries[] = $this->indexEntry($streamKey, $shardIndex, $shardLastmod);
                }
                continue;
            }

            if ($rowCount === 0) {
                foreach (array_keys($config['streams']) as $streamKey) {
                    Storage::disk('s3')->delete($this->shardKey($streamKey, $shardIndex));
                }

                SitemapShard::query()
                    ->where('source_table', $tableName)
                    ->where('shard_index', $shardIndex)
                    ->delete();

                continue;
            }

            $this->info(sprintf('  ├ shard %d/%d (rows=%d)', $shardIndex, $shardCount, $rowCount));

            $rows = $modelClass::query()
                ->withoutGlobalScopes()
                ->whereBetween('id', [$rangeStart, $rangeEnd])
                ->when($config['soft_deletes'], fn ($q) => $q->whereNull('deleted_at'))
                ->orderBy('id')
                ->select($config['select'])
                ->get();

            $streamXml = array_fill_keys(array_keys($config['streams']), $this->openUrlset());

            foreach ($rows as $row) {
                foreach ($config['streams'] as $streamKey => $routeName) {
                    $streamXml[$streamKey] .= $this->urlElement(
                        route($routeName, $row),
                        $row->updated_at,
                    );
                }
            }

            foreach ($streamXml as $streamKey => $xml) {
                Storage::disk('s3')->put(
                    $this->shardKey($streamKey, $shardIndex),
                    $xml . $this->closeUrlset(),
                    self::S3_PUT_OPTIONS,
                );

                $entries[] = $this->indexEntry($streamKey, $shardIndex, $maxUpdated ?? Carbon::now());
            }

            SitemapShard::query()->updateOrCreate(
                [
                    'source_table' => $tableName,
                    'shard_index'  => $shardIndex,
                ],
                [
                    'id_range_start' => $rangeStart,
                    'id_range_end'   => $rangeEnd,
                    'row_count'      => $rowCount,
                    'max_updated_at' => $maxUpdated,
                    'generated_at'   => Carbon::now(),
                ],
            );
        }

        return $entries;
    }

    /**
     * The gate query for a shard.
     *
     * @param class-string<Model>                                                                    $modelClass
     * @param array{streams: array<string, string>, select: array<int, string>, soft_deletes: bool} $config
     * @param int                                                                                    $rangeStart
     * @param int                                                                                    $rangeEnd
     *
     * @return Model
     */
    private function runGateQuery(string $modelClass, array $config, int $rangeStart, int $rangeEnd): Model
    {
        return $modelClass::query()
            ->withoutGlobalScopes()
            ->whereBetween('id', [$rangeStart, $rangeEnd])
            ->when($config['soft_deletes'], fn ($q) => $q->whereNull('deleted_at'))
            ->selectRaw('COUNT(*) AS row_count, MAX(updated_at) AS max_updated_at')
            ->first();
    }

    /**
     * Whether the shard matches its stored state.
     *
     * @param ?SitemapShard    $stored
     * @param int              $rowCount
     * @param ?CarbonInterface $maxUpdated
     *
     * @return bool
     */
    private function shardUnchanged(?SitemapShard $stored, int $rowCount, ?CarbonInterface $maxUpdated): bool
    {
        if ($stored === null) {
            return false;
        }

        if ((int) $stored->row_count !== $rowCount) {
            return false;
        }

        if ($stored->max_updated_at === null && $maxUpdated === null) {
            return true;
        }

        if ($stored->max_updated_at === null || $maxUpdated === null) {
            return false;
        }

        return $stored->max_updated_at->equalTo($maxUpdated);
    }

    /**
     * The static-pages sitemap.
     *
     * @return array{loc: string, lastmod: CarbonInterface}
     */
    private function generateStaticPagesSitemap(): array
    {
        $this->info('- static_pages: 1 shard');

        $now = Carbon::now();

        $urls = [
            route('home'),
            route('schedule'),
            route('search.index'),
            route('anime.index'),
            route('manga.index'),
            route('games.index'),
            route('legal.privacy-policy'),
            route('legal.terms-of-use'),
            route('misc.team'),
            route('misc.projects'),
            route('misc.contact'),
            route('misc.press-kit'),
        ];

        $xml = $this->openUrlset();

        foreach ($urls as $url) {
            $xml .= $this->urlElement($url, $now);
        }

        $xml .= $this->closeUrlset();

        Storage::disk('s3')->put(
            $this->shardKey('static_pages', 1),
            $xml,
            self::S3_PUT_OPTIONS,
        );

        return $this->indexEntry('static_pages', 1, $now);
    }

    /**
     * Upload the sitemap index.
     *
     * @param array<int, array{loc: string, lastmod: CarbonInterface}> $entries
     *
     * @return void
     */
    private function uploadSitemapIndex(array $entries): void
    {
        $this->info('- Uploading sitemap index');

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($entries as $entry) {
            $xml .= '<sitemap>';
            $xml .= '<loc>' . $entry['loc'] . '</loc>';
            $xml .= '<lastmod>' . $entry['lastmod']->format(DateTimeInterface::ATOM) . '</lastmod>';
            $xml .= '</sitemap>';
        }

        $xml .= '</sitemapindex>';

        Storage::disk('s3')->put(
            self::S3_PREFIX . '/sitemap_index.xml',
            $xml,
            self::S3_PUT_OPTIONS,
        );
    }

    /**
     * An index entry for a stream and shard.
     *
     * @param string          $streamKey
     * @param int             $shardIndex
     * @param CarbonInterface $lastmod
     *
     * @return array{loc: string, lastmod: CarbonInterface}
     */
    private function indexEntry(string $streamKey, int $shardIndex, CarbonInterface $lastmod): array
    {
        return [
            'loc'     => Storage::disk('s3')->url($this->shardKey($streamKey, $shardIndex)),
            'lastmod' => $lastmod,
        ];
    }

    /**
     * The S3 key for a shard.
     *
     * @param string $streamKey
     * @param int    $shardIndex
     *
     * @return string
     */
    private function shardKey(string $streamKey, int $shardIndex): string
    {
        return self::S3_PREFIX . '/' . $this->shardFilename($streamKey, $shardIndex);
    }

    /**
     * The filename for a shard.
     *
     * @param string $streamKey
     * @param int    $shardIndex
     *
     * @return string
     */
    private function shardFilename(string $streamKey, int $shardIndex): string
    {
        return $streamKey . '_' . $shardIndex . '_sitemap.xml';
    }

    /**
     * The opening tag for a urlset document.
     *
     * @return string
     */
    private function openUrlset(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'
            . ' xmlns:xhtml="http://www.w3.org/1999/xhtml"'
            . ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"'
            . ' xmlns:video="http://www.google.com/schemas/sitemap-video/1.1"'
            . ' xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">';
    }

    /**
     * The closing tag for a urlset document.
     *
     * @return string
     */
    private function closeUrlset(): string
    {
        return '</urlset>';
    }

    /**
     * A single <url> element.
     *
     * @param string          $loc
     * @param CarbonInterface $lastmod
     *
     * @return string
     */
    private function urlElement(string $loc, CarbonInterface $lastmod): string
    {
        return '<url>'
            . '<loc>' . $loc . '</loc>'
            . '<changefreq>' . self::CHANGE_FREQUENCY . '</changefreq>'
            . '<lastmod>' . $lastmod->format(DateTimeInterface::ATOM) . '</lastmod>'
            . '</url>';
    }
}
