<?php

namespace App\Console\Commands\ELB;

use Cog\Laravel\Love\Reacter\Models\Reacter;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportReactors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:elb_love_reacters';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports love reacters from the ELB database.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        // Below code is necessary to switch between DB connections
        // since normal on() method doesn't work.
        // Stupid package uses config for connection.
        // Fuck this stupid backwards-ass package.
        config(['love.storage.database.connection' => 'elb']);

        Reacter::orderBy('id')
            ->chunk(1000, function (Collection $reacters) {
                /** @var Reacter $reacter */
                foreach ($reacters as $reacter) {
                    try {
                        config(['love.storage.database.connection' => 'mysql']);

                        Reacter::updateOrCreate([
                            'id' => $reacter->id,
                            'type' => $reacter->type
                        ], [
                            'id' => $reacter->id,
                            'type' => $reacter->type
                        ]);
                    } catch (Exception $exception) {
                        print 'Exception at: ' . $reacter->id . PHP_EOL;
                        print $exception->getMessage() . PHP_EOL;
                    }

                    print 'Added: ' . $reacter->id . PHP_EOL;
                }
            });

        return Command::SUCCESS;
    }
}
