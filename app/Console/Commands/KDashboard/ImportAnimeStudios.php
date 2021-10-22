<?php

namespace App\Console\Commands\KDashboard;

use App\Jobs\ProcessImportAnimeStudio;
use App\Models\KDashboard\AnimeProducer as KAnimeProducer;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportAnimeStudios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:anime-studios';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports the anime studios from the KDashboard database.';

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
        KAnimeProducer::chunk(1000, function (Collection $kAnimeProducers) {
            ProcessImportAnimeStudio::dispatch($kAnimeProducers);
        });

        return Command::SUCCESS;
    }
}
