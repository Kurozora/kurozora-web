<?php

namespace App\Console\Commands\KDashboard;

use App\Jobs\ProcessImportAnimeStaff;
use App\Models\KDashboard\AnimeStaff as KAnimeStaff;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportAnimeStaff extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:kdashboard_anime_staff';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports the anime staff from the KDashboard database.';

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
        KAnimeStaff::where([
            ['id', '>', 150000],
        ])->chunk(1000, function (Collection $kAnimeStaff) {
            ProcessImportAnimeStaff::dispatch($kAnimeStaff);
        });

        return Command::SUCCESS;
    }
}
