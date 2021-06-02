<?php

namespace App\Console\Commands\KDashboard;

use App\Jobs\ProcessImportAnimeGenre;
use App\Models\KDashboard\MediaGenre as KMediaGenre;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportAnimeGenres extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:anime-genres';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports the anime genres from the KDashboard database.';

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
        KMediaGenre::chunk(1000, function (Collection $kAnimes) {
            ProcessImportAnimeGenre::dispatch($kAnimes);
        });

        return 1;
    }
}
