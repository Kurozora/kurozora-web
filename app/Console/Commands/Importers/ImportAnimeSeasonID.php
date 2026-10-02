<?php

namespace App\Console\Commands\Importers;

use App\Models\Anime;
use App\Models\Season;
use Illuminate\Console\Command;
use Laravel\Telescope\Telescope;
use Pulse;
use XMLReader;

class ImportAnimeSeasonID extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:anime_season_ids
                            {path? : Path to the anime-list XML; defaults to storage/app/anime-list-full.xml}
                            {--force : Also overwrite mappings that differ from the auto-default (1/0)}
                            {--dry-run : Report the seasons that would be updated, without writing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports TVDB season mappings from the anime-lists XML onto seasons matched by AniDB id.';

    /**
     * The database connection used for writes.
     *
     * @var string
     */
    protected const string CONNECTION = 'elb';

    /**
     * The chunk size used when walking the anime table.
     *
     * @var int
     */
    protected const int CHUNK_SIZE = 500;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Pulse::stopRecording();
        Telescope::stopRecording();

        $path = $this->argument('path') ?? storage_path('app/anime-list-full.xml');

        if (!file_exists($path)) {
            $this->error('No anime-list XML at ' . $path . '. Download anime-list-full.xml from ScudLee/anime-lists first.');

            return Command::FAILURE;
        }

        $mappings = $this->parseMappings($path);
        $this->info(number_format(count($mappings)) . ' TVDB mappings parsed.');

        $force = (bool) $this->option('force');
        $dryRun = (bool) $this->option('dry-run');
        $counts = [
            'matched' => 0,
            'seasons updated' => 0,
            'tvdb ids set' => 0,
            'kept manual mapping (use --force)' => 0,
            'skipped multi-season anime' => 0,
        ];

        $query = Anime::on(self::CONNECTION)
            ->withoutGlobalScopes()
            ->whereNotNull('anidb_id')
            ->select(['id', 'anidb_id', 'tvdb_id'])
            ->with(['seasons' => function ($relation) {
                $relation->withoutGlobalScopes()
                    ->select(['id', 'anime_id', 'tvdb_season', 'tvdb_episode_offset']);
            }]);

        $progressBar = $this->output->createProgressBar($query->count());
        $progressBar->start();

        $query->chunkById(self::CHUNK_SIZE, function ($animes) use ($mappings, $force, $dryRun, &$counts, $progressBar) {
            $seasonUpdates = [];

            foreach ($animes as $anime) {
                $progressBar->advance();
                $mapping = $mappings[(int) $anime->anidb_id] ?? null;

                if ($mapping === null) {
                    continue;
                }

                $counts['matched']++;

                if ($anime->tvdb_id === null && $mapping['tvdb_id'] !== null) {
                    $counts['tvdb ids set']++;

                    if (!$dryRun) {
                        $anime->update(['tvdb_id' => $mapping['tvdb_id']]);
                    }
                }

                // The list maps one AniDB entry to one TVDB season. An anime
                // holding several seasons needs its mapping imported manually.
                if ($anime->seasons->count() > 1) {
                    $counts['skipped multi-season anime']++;

                    continue;
                }

                foreach ($anime->seasons as $season) {
                    $currentSeason = $season->tvdb_season === null ? null : (int) $season->tvdb_season;
                    $currentOffset = (int) ($season->tvdb_episode_offset ?? 0);

                    if ($currentSeason === $mapping['season'] && $currentOffset === $mapping['offset']) {
                        continue;
                    }

                    $isAutoDefault = $currentSeason === null || ($currentSeason === 1 && $currentOffset === 0);

                    if (!$isAutoDefault && !$force) {
                        $counts['kept manual mapping (use --force)']++;

                        continue;
                    }

                    $counts['seasons updated']++;
                    $seasonUpdates[$mapping['season'] . '|' . $mapping['offset']][] = $season->id;
                }
            }

            if ($dryRun) {
                return;
            }

            foreach ($seasonUpdates as $pair => $seasonIDs) {
                [$tvdbSeason, $episodeOffset] = explode('|', $pair);

                Season::on(self::CONNECTION)
                    ->withoutGlobalScopes()
                    ->whereIn('id', $seasonIDs)
                    ->update([
                        'tvdb_season' => (int) $tvdbSeason,
                        'tvdb_episode_offset' => (int) $episodeOffset,
                    ]);
            }
        });

        $progressBar->finish();
        $this->newLine(2);

        foreach ($counts as $label => $count) {
            $this->info(ucfirst($label) . ': ' . number_format($count));
        }

        if ($dryRun) {
            $this->comment('Dry run; nothing was written.');
        }

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }

    /**
     * The usable TVDB mappings in the XML keyed by AniDB id.
     *
     * @param string $path
     * @return array<int, array{tvdb_id: int|null, season: int, offset: int}>
     */
    protected function parseMappings(string $path): array
    {
        $reader = new XMLReader();
        $reader->open($path);

        $mappings = [];

        while ($reader->read()) {
            if ($reader->nodeType !== XMLReader::ELEMENT || $reader->name !== 'anime') {
                continue;
            }

            $anidbID = $reader->getAttribute('anidbid');
            $tvdbID = $reader->getAttribute('tvdbid');
            $defaultSeason = $reader->getAttribute('defaulttvdbseason');
            $episodeOffset = $reader->getAttribute('episodeoffset') ?? '0';

            if (!ctype_digit((string) $anidbID) || !ctype_digit((string) $tvdbID) || !ctype_digit((string) $defaultSeason)) {
                continue;
            }

            $season = (int) $defaultSeason;
            $offset = (int) $episodeOffset;

            // The columns are signed tiny integers.
            if ($season > 127 || $offset > 127 || $offset < -128) {
                continue;
            }

            $mappings[(int) $anidbID] = [
                'tvdb_id' => (int) $tvdbID,
                'season' => $season,
                'offset' => $offset,
            ];
        }

        $reader->close();

        return $mappings;
    }
}
