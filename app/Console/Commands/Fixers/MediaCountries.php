<?php

namespace App\Console\Commands\Fixers;

use App\Models\Anime;
use App\Models\Manga;
use Generator;
use Illuminate\Console\Command;
use Laravel\Telescope\Telescope;
use Pulse;

class MediaCountries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:media_countries
                            {path? : Path to an AniDB titles dump, defaulting to the newest storage/app/anidb-titles-*.xml.gz}
                            {--dry-run : Report what would change without writing it.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrects the country of origin of anime and manga.';

    /**
     * The publication types mapped to their country of origin.
     *
     * @var string[]
     */
    protected const array TYPE_COUNTRIES = [
        'manga' => 'jp',
        'doujinshi' => 'jp',
        'light novel' => 'jp',
        'novel' => 'jp',
        'one-shot' => 'jp',
        'manhwa' => 'kr',
        'manhua' => 'cn',
        'oel' => 'us',
    ];

    /**
     * The main title languages mapped to their country of origin.
     *
     * @var string[]
     */
    protected const array TITLE_COUNTRIES = [
        'x-jat' => 'jp',
        'ja' => 'jp',
        'x-zht' => 'cn',
        'zh' => 'cn',
        'zh-Hans' => 'cn',
        'zh-Hant' => 'tw',
        'x-kot' => 'kr',
        'ko' => 'kr',
    ];

    /**
     * The number of rows read per chunk.
     *
     * @var int
     */
    protected const int CHUNK_SIZE = 1000;

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
            $this->warn('Dry run. Nothing will be written.');
        }

        $this->fixManga($isDryRun);
        $this->newLine();
        $this->fixAnime($isDryRun);

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }

    /**
     * Correct the country of every manga from its publication type.
     *
     * @param bool $isDryRun
     * @return void
     */
    protected function fixManga(bool $isDryRun): void
    {
        $this->info('Manga');

        $corrections = [];
        $unmapped = 0;

        Manga::withoutGlobalScopes()
            ->whereNull('deleted_at')
            ->with('mediaType')
            ->select(['id', 'country_id', 'media_type_id'])
            ->orderBy('id')
            ->chunkById(self::CHUNK_SIZE, function ($mangas) use (&$corrections, &$unmapped) {
                foreach ($mangas as $manga) {
                    $type = strtolower($manga->mediaType?->name ?? '');
                    $country = self::TYPE_COUNTRIES[$type] ?? null;

                    if ($country === null) {
                        $unmapped++;
                        continue;
                    }

                    if ($country !== $manga->country_id) {
                        $corrections[$country][] = $manga->id;
                    }
                }
            });

        $this->report($corrections, $unmapped, 'manga have a publication type with no mapped country.', $isDryRun, Manga::class);
    }

    /**
     * Correct the country of every anime from its main title's language.
     *
     * @param bool $isDryRun
     * @return void
     */
    protected function fixAnime(bool $isDryRun): void
    {
        $this->info('Anime');

        $path = $this->dumpPath();

        if ($path === null) {
            $this->error('  No AniDB titles dump found. Skipping anime.');

            return;
        }

        $this->line('  Reading ' . basename($path) . '...');

        $countries = [];

        foreach ($this->readMainTitles($path) as $anidbID => $language) {
            $country = self::TITLE_COUNTRIES[$language] ?? null;

            if ($country !== null) {
                $countries[$anidbID] = $country;
            }
        }

        $this->line('  ' . count($countries) . ' anime classified by the dump.');

        $corrections = [];
        $unmapped = 0;

        Anime::withoutGlobalScopes()
            ->whereNull('deleted_at')
            ->whereNotNull('anidb_id')
            ->select(['id', 'country_id', 'anidb_id'])
            ->orderBy('id')
            ->chunkById(self::CHUNK_SIZE, function ($animes) use (&$corrections, &$unmapped, $countries) {
                foreach ($animes as $anime) {
                    $country = $countries[$anime->anidb_id] ?? null;

                    if ($country === null) {
                        $unmapped++;
                        continue;
                    }

                    if ($country !== $anime->country_id) {
                        $corrections[$country][] = $anime->id;
                    }
                }
            });

        $this->report($corrections, $unmapped, 'anime are absent from the dump and were left alone.', $isDryRun, Anime::class);
    }

    /**
     * Apply and report the corrections of a single model.
     *
     * @param array  $corrections
     * @param int    $unmapped
     * @param string $caveat
     * @param bool   $isDryRun
     * @param string $model
     * @return void
     */
    protected function report(array $corrections, int $unmapped, string $caveat, bool $isDryRun, string $model): void
    {
        ksort($corrections);
        $total = 0;

        foreach ($corrections as $country => $ids) {
            $this->line('  ' . str_pad($country, 4) . count($ids));
            $total += count($ids);

            if (!$isDryRun) {
                foreach (array_chunk($ids, self::CHUNK_SIZE) as $chunk) {
                    $model::withoutGlobalScopes()
                        ->whereIn('id', $chunk)
                        ->update(['country_id' => $country]);
                }
            }
        }

        if ($unmapped > 0) {
            $this->warn('  ' . $unmapped . ' ' . $caveat);
        }

        $this->line('  ' . ($isDryRun ? 'Would correct: ' : 'Corrected: ') . $total);
    }

    /**
     * Resolve the newest AniDB titles dump in storage when no path is given.
     *
     * @return string|null
     */
    protected function dumpPath(): ?string
    {
        $path = $this->argument('path');

        if (!empty($path)) {
            return is_file($path) ? $path : null;
        }

        $matches = glob(storage_path('app/anidb-titles-*.xml.gz')) ?: [];
        rsort($matches);

        return $matches[0] ?? null;
    }

    /**
     * Stream the language of every entry's main title.
     *
     * @param string $path
     * @return Generator
     */
    protected function readMainTitles(string $path): Generator
    {
        $handle = gzopen($path, 'rb');

        if ($handle === false) {
            return;
        }

        try {
            $anidbID = null;

            while (($line = gzgets($handle)) !== false) {
                if (preg_match('/<anime aid="(\d+)"/', $line, $matches)) {
                    $anidbID = (int) $matches[1];
                    continue;
                }

                if ($anidbID === null || !str_contains($line, 'type="main"')) {
                    continue;
                }

                if (preg_match('/xml:lang="([\w-]+)"/', $line, $matches)) {
                    yield $anidbID => $matches[1];
                }
            }
        } finally {
            gzclose($handle);
        }
    }
}
