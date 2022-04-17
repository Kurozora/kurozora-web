<?php

namespace App\Console\Commands\KDashboard;

use App\Jobs\ProcessImportStudio;
use App\Models\KDashboard\ProducerMagazine as KStudio;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportStudios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:kdashboard_studios';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports the studios from the KDashboard database.';

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
        KStudio::chunk(1000, function (Collection $kStudios) {
            ProcessImportStudio::dispatch($kStudios);
        });

//        Studio::chunk(1000, function (Collection $studios) {
//            /** @var Studio[] $studios */
//            foreach ($studios as $studio) {
//                $kStudio = KStudio::firstWhere('id', '=', $studio->mal_id);
//
//                if (empty($kStudio)) {
//                    \Log::warning('Didn’t find: ' . $studio->name);
//                    \Log::info('studio id: ' . $studio->mal_id);
//                }
//            }
//        });

        return Command::SUCCESS;
    }
}
