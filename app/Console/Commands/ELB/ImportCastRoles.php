<?php

namespace App\Console\Commands\ELB;

use App\Models\CastRole;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportCastRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:elb_cast_roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports cast roles from the ELB database.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        CastRole::on('elb')
            ->orderBy('id')
            ->chunk(1000, function (Collection $castRoles) {
                /** @var CastRole $castRole */
                foreach ($castRoles as $castRole) {
                    try {
                        CastRole::updateOrCreate([
                            'id' => $castRole->id,
                            'name' => $castRole->name,
                        ], [
                            'id' => $castRole->id,
                            'name' => $castRole->name,
                            'description' => $castRole->description,
                        ]);
                    } catch (Exception $exception) {
                        print 'Exception at: ' . $castRole->id . PHP_EOL;
                        print $exception->getMessage() . PHP_EOL;
                    }

                    print 'Added: ' . $castRole->id . PHP_EOL;
                }
            });

        return 0;
    }
}
