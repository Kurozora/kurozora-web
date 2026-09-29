<?php

namespace App\Console\Commands\Importers;

use App\Enums\LanguageSupportType;
use App\Models\Anime;
use App\Models\Language;
use App\Models\MediaLanguage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Laravel\Telescope\Telescope;
use Pulse;

class ImportAnimeLanguages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:anime_languages
                            {path? : Path to a local dubInfo.json, defaulting to the published one}
                            {--dry-run : Report what would be written without writing it.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports the audio and subtitle languages anime are available in.';

    /**
     * The published dub listing.
     *
     * @var string
     */
    protected const string SOURCE = 'https://raw.githubusercontent.com/MAL-Dubs/MAL-Dubs/main/data/dubInfo.json';

    /**
     * The number of rows written per chunk.
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

        $isDryRun = (bool) $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('Dry run. Nothing will be written.');
        }

        $dubbed = $this->readDubbedIDs();

        if ($dubbed === null) {
            $this->error('Could not read the dub listing. Adios...');

            return Command::FAILURE;
        }

        $languageIDs = Language::withoutGlobalScopes()->pluck('id', 'code');
        $englishID = $languageIDs->get('en');

        if (empty($englishID)) {
            $this->error('The English language row is missing. Adios...');

            return Command::FAILURE;
        }

        $this->info(count($dubbed) . ' dubbed anime listed.');

        $morphClass = (new Anime)->getMorphClass();
        $written = 0;
        $counts = ['origin' => 0, 'dub' => 0, 'subtitle' => 0, 'unmapped' => 0];

        Anime::withoutGlobalScopes()
            ->whereNull('deleted_at')
            ->with('languages')
            ->select(['id', 'mal_id', 'country_id'])
            ->orderBy('id')
            ->chunkById(self::CHUNK_SIZE, function ($anime) use (&$written, &$counts, $dubbed, $morphClass, $languageIDs, $englishID, $isDryRun) {
                $rows = [];

                foreach ($anime as $entry) {
                    $originCode = origin_language($entry->country_id);
                    $originID = $originCode === null ? null : $languageIDs->get($originCode);

                    if ($originID === null) {
                        $counts['unmapped']++;
                    } else {
                        $rows[] = ['model_id' => $entry->id, 'model_type' => $morphClass, 'language_id' => $originID, 'type' => LanguageSupportType::Audio];
                        $counts['origin']++;
                    }

                    if ($entry->mal_id !== null && isset($dubbed[$entry->mal_id])) {
                        $rows[] = ['model_id' => $entry->id, 'model_type' => $morphClass, 'language_id' => $englishID, 'type' => LanguageSupportType::Audio];
                        $counts['dub']++;
                    }

                    // A localized title means the entry is followable in that language.
                    foreach ($entry->languages->unique('id') as $language) {
                        if ($language->code === $originCode) {
                            continue;
                        }

                        $rows[] = ['model_id' => $entry->id, 'model_type' => $morphClass, 'language_id' => $language->id, 'type' => LanguageSupportType::Subtitles];
                        $counts['subtitle']++;
                    }
                }

                $written += count($rows);

                if ($isDryRun || empty($rows)) {
                    return;
                }

                $now = now();

                DB::table(MediaLanguage::TABLE_NAME)->insertOrIgnore(array_map(
                    fn (array $row) => $row + ['created_at' => $now, 'updated_at' => $now],
                    $rows,
                ));
            });

        $this->line('  ' . $counts['origin'] . ' origin audio track(s).');
        $this->line('  ' . $counts['dub'] . ' English dub(s).');
        $this->line('  ' . $counts['subtitle'] . ' subtitle track(s).');

        if ($counts['unmapped'] > 0) {
            $this->warn('  ' . $counts['unmapped'] . ' anime have a country with no mapped language and got no audio.');
        }

        $this->newLine();
        $this->info(($isDryRun ? 'Would write: ' : 'Wrote: ') . $written . ' media language(s).');

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }

    /**
     * Read the MyAnimeList ids that have an English dub.
     *
     * @return array|null
     */
    protected function readDubbedIDs(): ?array
    {
        $path = $this->argument('path');

        if (!empty($path)) {
            $body = is_file($path) ? file_get_contents($path) : null;
        } else {
            $response = Http::timeout(60)->get(self::SOURCE);
            $body = $response->successful() ? $response->body() : null;
        }

        if ($body === null) {
            return null;
        }

        $data = json_decode($body, true);

        if (!is_array($data)) {
            return null;
        }

        // A partial dub still means the audio track exists.
        return array_flip(array_merge($data['dubbed'] ?? [], $data['incomplete'] ?? []));
    }
}
