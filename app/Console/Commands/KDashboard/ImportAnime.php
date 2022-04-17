<?php

namespace App\Console\Commands\KDashboard;

use App\Jobs\ProcessImportAnime;
use App\Models\KDashboard\Anime as KAnime;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportAnime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:kdashboard_anime';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports the anime from the KDashboard database.';

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
        KAnime::chunk(1000, function (Collection $kAnimes) {
            ProcessImportAnime::dispatch($kAnimes);
        });

        // Clean invalid anime
//        Anime::chunk(1000, function (Collection $animes) {
//        /** @var Anime[] $animes */
//            foreach ($animes as $anime) {
//                $kAnime = KAnime::firstWhere('id', '=', $anime->mal_id);
//
//                if (empty($kAnime)) {
//                    \Log::info($anime->mal_id);
//
//                    $altAnime = Anime::firstWhere([
//                        ['mal_id', '!=', $anime->mal_id],
//                        ['original_title', '=', $anime->original_title],
//                    ]);
//
//                    if (empty($altAnime)) {
////                        \Log::warning('Didn’t find: ' . $anime->original_title);
//                    } else {
//                        // Grab the slug
//                        $animeSlug = $anime->slug;
//
//                        // Delete the invalid anime
//                        $anime->delete();
//
//                        // Update the slug of the alternative
//                        $altAnime->update([
//                            'slug' => $animeSlug
//                        ]);
//                    }
//                }
//            }
//        });

        return Command::SUCCESS;
    }
}
