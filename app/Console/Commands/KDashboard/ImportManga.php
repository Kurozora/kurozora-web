<?php

namespace App\Console\Commands\KDashboard;

use App\Jobs\ProcessImportManga;
use App\Models\KDashboard\Manga as KManga;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportManga extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:kdashboard_manga';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports the manga from the KDashboard database.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        KManga::chunk(1000, function (Collection $kMangas) {
            ProcessImportManga::dispatch($kMangas);
        });

        // Clean invalid manga
//        Manga::chunk(1000, function (Collection $mangas) {
//        /** @var Manga[] $mangas */
//            foreach ($mangas as $manga) {
//                $kManga = KManga::firstWhere('id', '=', $manga->mal_id);
//
//                if (empty($kManga)) {
//                    \Log::info($manga->mal_id);
//
//                    $altManga = Manga::firstWhere([
//                        ['mal_id', '!=', $manga->mal_id],
//                        ['original_title', '=', $manga->original_title],
//                    ]);
//
//                    if (empty($altManga)) {
////                        \Log::warning('Didn’t find: ' . $manga->original_title);
//                    } else {
//                        // Grab the slug
//                        $mangaSlug = $manga->slug;
//
//                        // Delete the invalid manga
//                        $manga->delete();
//
//                        // Update the slug of the alternative
//                        $altManga->update([
//                            'slug' => $mangaSlug
//                        ]);
//                    }
//                }
//            }
//        });

        return Command::SUCCESS;
    }
}
