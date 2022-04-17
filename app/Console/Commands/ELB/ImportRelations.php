<?php

namespace App\Console\Commands\ELB;

use App\Models\Relation;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportRelations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:elb_relations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports relationships from the ELB database.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Relation::on('elb')
            ->orderBy('id')
            ->chunk(1000, function (Collection $relations) {
                /** @var Relation $relation */
                foreach ($relations as $relation) {
                    try {
                        Relation::updateOrCreate([
                            'id' => $relation->id,
                            'name' => $relation->name,
                        ], [
                            'id' => $relation->id,
                            'name' => $relation->name,
                            'description' => $relation->description,
                        ]);
                    } catch (Exception $exception) {
                        print 'Exception at: ' . $relation->id . PHP_EOL;
                        print $exception->getMessage() . PHP_EOL;
                    }

                    print 'Added: ' . $relation->id . PHP_EOL;
                }
            });

        return 0;
    }
}
