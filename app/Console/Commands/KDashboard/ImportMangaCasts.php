<?php

namespace App\Console\Commands\KDashboard;

use App\Jobs\ProcessImportMangaCast;
use App\Models\KDashboard\MangaCharacter as KMangaCast;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportMangaCasts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:kdashboard_manga_cast';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports the manga casts from the KDashboard database.';

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
        KMangaCast::chunk(1000, function (Collection $kMangaCasts) {
            ProcessImportMangaCast::dispatch($kMangaCasts);
        });

        return Command::SUCCESS;
    }
}
