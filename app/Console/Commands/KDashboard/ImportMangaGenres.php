<?php

namespace App\Console\Commands\KDashboard;

use App\Jobs\ProcessImportMangaGenre;
use App\Models\KDashboard\MediaGenre as KMediaGenre;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportMangaGenres extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:kdashboard_manga_genres';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports the manga genres from the KDashboard database.';

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
        KMediaGenre::where('type', 'manga')
            ->chunk(1000, function (Collection $kMediaGenres) {
                ProcessImportMangaGenre::dispatch($kMediaGenres);
            });

        return Command::SUCCESS;
    }
}
