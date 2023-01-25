<?php

namespace App\Console\Commands\KDashboard;

use App\Jobs\ProcessImportMangaStaff;
use App\Models\KDashboard\PeopleManga as KMangaStaff;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportMangaStaff extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:kdashboard_manga_staff';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports the manga staff from the KDashboard database.';

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
        KMangaStaff::chunk(1000, function (Collection $kMangaStaff) {
            ProcessImportMangaStaff::dispatch($kMangaStaff);
        });

        return Command::SUCCESS;
    }
}
