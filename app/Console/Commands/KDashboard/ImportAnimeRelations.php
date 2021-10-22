<?php

namespace App\Console\Commands\KDashboard;

use App\Jobs\ProcessImportAnimeRelations;
use App\Models\KDashboard\MediaRelated as KMediaRelated;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportAnimeRelations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:anime-relations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports the anime relations from the KDashboard database.';

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
        KMediaRelated::where([
            ['media_type', 'anime'],
            ['related_type', 'anime'],
        ])->chunk(1000, function (Collection $kMediaRelated) {
            ProcessImportAnimeRelations::dispatch($kMediaRelated);
        });

        return Command::SUCCESS;
    }
}
