<?php

namespace App\Console\Commands\KDashboard;

use App\Jobs\ProcessImportPerson;
use App\Models\KDashboard\People as KPeople;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportPeople extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:people';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports the people from the KDashboard database.';

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
        KPeople::chunk(1000, function (Collection $kPeople) {
            ProcessImportPerson::dispatch($kPeople);
        });

        return 1;
    }
}
