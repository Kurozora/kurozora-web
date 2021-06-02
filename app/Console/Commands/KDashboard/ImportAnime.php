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
    protected $signature = 'import:anime';

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

        return 1;
    }
}
