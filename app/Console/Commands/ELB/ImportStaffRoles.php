<?php

namespace App\Console\Commands\ELB;

use App\Models\StaffRole;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportStaffRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:elb_staff_roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports staff roles from the ELB database.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        StaffRole::on('elb')
            ->orderBy('id')
            ->chunk(1000, function (Collection $staffRoles) {
                /** @var StaffRole $staffRole */
                foreach ($staffRoles as $staffRole) {
                    try {
                        StaffRole::updateOrCreate([
                            'id' => $staffRole->id,
                            'name' => $staffRole->name,
                        ], [
                            'id' => $staffRole->id,
                            'name' => $staffRole->name,
                            'description' => $staffRole->description,
                        ]);
                    } catch (Exception $exception) {
                        print 'Exception at: ' . $staffRole->id . PHP_EOL;
                        print $exception->getMessage() . PHP_EOL;
                    }
                }

                print 'Added: ' . $staffRole->id . PHP_EOL;
            });

        return 0;
    }
}
