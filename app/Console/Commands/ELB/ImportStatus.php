<?php

namespace App\Console\Commands\ELB;

use App\Models\Status;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:elb_status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports status from the ELB database.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Status::on('elb')
            ->orderBy('id')
            ->chunk(1000, function (Collection $statuses) {
                /** @var Status $status */
                foreach ($statuses as $status) {
                    try {
                        Status::updateOrCreate([
                            'id' => $status->id,
                            'type' => $status->type,
                            'name' => $status->name,
                        ], [
                            'id' => $status->id,
                            'type' => $status->type,
                            'name' => $status->name,
                            'description' => $status->description,
                            'color' => $status->color
                        ]);
                    } catch (Exception $exception) {
                        print 'Exception at: ' . $status->id . PHP_EOL;
                        print $exception->getMessage() . PHP_EOL;
                    }

                    print 'Added: ' . $status->id . PHP_EOL;
                }
            });

        return 0;
    }
}
