<?php

namespace App\Console\Commands\KDashboard;

use App\Jobs\ProcessImportCharacter;
use App\Models\KDashboard\Character as KCharacter;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportCharacters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:kdashboard_characters';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports the characters from the KDashboard database.';

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
        KCharacter::chunk(1000, function (Collection $kCharacters) {
            ProcessImportCharacter::dispatch($kCharacters);
        });

        return Command::SUCCESS;
    }
}
