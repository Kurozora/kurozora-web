<?php

namespace App\Console\Commands\KDashboard;

use App\Jobs\ProcessImportAnimeCast;
use App\Models\KDashboard\AnimeCharacter as KAnimeCast;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportAnimeCasts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:anime-cast';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports the anime casts from the KDashboard database.';

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
        KAnimeCast::where([
            ['id', '>', 250000],
        ])->chunk(1000, function (Collection $kAnimeCasts) {
            ProcessImportAnimeCast::dispatch($kAnimeCasts);
        });

        return 1;
    }
}
