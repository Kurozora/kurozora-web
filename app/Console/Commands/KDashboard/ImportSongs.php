<?php

namespace App\Console\Commands\KDashboard;

use App\Jobs\ProcessImportSong;
use App\Models\KDashboard\Song as KSong;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportSongs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:songs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports the songs from the KDashboard database';

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
        KSong::chunk(1000, function (Collection $kPeople) {
            ProcessImportSong::dispatch($kPeople);
        });

        return 1;
    }
}
