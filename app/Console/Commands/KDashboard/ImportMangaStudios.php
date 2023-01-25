<?php

namespace App\Console\Commands\KDashboard;

use App\Jobs\ProcessImportMangaStudio;
use App\Models\KDashboard\MangaMagazine as KMangaProducer;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportMangaStudios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:kdashboard_manga_studios';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports the manga studios from the KDashboard database.';

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
        KMangaProducer::chunk(1000, function (Collection $kMangaProducers) {
            ProcessImportMangaStudio::dispatch($kMangaProducers);
        });

        return Command::SUCCESS;
    }
}
