<?php

namespace App\Console\Commands\Importers;

use App\Enums\LanguageSupportType;
use App\Models\Language;
use App\Models\Manga;
use App\Models\MediaLanguage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Laravel\Telescope\Telescope;
use Pulse;

class ImportMangaLanguages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:manga_languages
                            {--dry-run : Report what would be written without writing it.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports the languages manga are published in.';

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

        $languageIDs = Language::withoutGlobalScopes()->pluck('id', 'code');
        $morphClass = (new Manga)->getMorphClass();
        $written = 0;
        $counts = ['origin' => 0, 'translation' => 0, 'unmapped' => 0];

        Manga::withoutGlobalScopes()
            ->whereNull('deleted_at')
            ->with('languages')
            ->select(['id', 'country_id'])
            ->orderBy('id')
            ->chunkById(self::CHUNK_SIZE, function ($mangas) use (&$written, &$counts, $morphClass, $languageIDs, $isDryRun) {
                $rows = [];

                foreach ($mangas as $manga) {
                    $originCode = origin_language($manga->country_id);
                    $originID = $originCode === null ? null : $languageIDs->get($originCode);

                    if ($originID === null) {
                        $counts['unmapped']++;
                    } else {
                        $rows[] = ['model_id' => $manga->id, 'model_type' => $morphClass, 'language_id' => $originID, 'type' => LanguageSupportType::Text];
                        $counts['origin']++;
                    }

                    // A localized title means the manga is published in that language.
                    foreach ($manga->languages->unique('id') as $language) {
                        if ($language->code === $originCode) {
                            continue;
                        }

                        $rows[] = ['model_id' => $manga->id, 'model_type' => $morphClass, 'language_id' => $language->id, 'type' => LanguageSupportType::Text];
                        $counts['translation']++;
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

        $this->line('  ' . $counts['origin'] . ' original language(s).');
        $this->line('  ' . $counts['translation'] . ' translation(s).');

        if ($counts['unmapped'] > 0) {
            $this->warn('  ' . $counts['unmapped'] . ' manga have a country with no mapped language and got no original language.');
        }

        $this->newLine();
        $this->info(($isDryRun ? 'Would write: ' : 'Wrote: ') . $written . ' media language(s).');

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }
}
