<?php

namespace App\Console\Commands\ELB;

use App\Models\MediaRelation;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportMediaRelations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:elb_media_relations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports media relations from ELB database.';

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
        MediaRelation::on('elb')
            ->orderBy('id')
            ->chunk(1000, function (Collection $mediaRelations) {
                /** @var MediaRelation $mediaRelation */
                foreach ($mediaRelations as $mediaRelation) {
                    try {
                        MediaRelation::updateOrCreate([
                            'model_id' => $mediaRelation->model_id,
                            'model_type' => $mediaRelation->model_type,
                            'relation_id' => $mediaRelation->relation_id,
                            'related_id' => $mediaRelation->related_id,
                            'related_type' => $mediaRelation->related_type,
                        ], [
                            'id' => $mediaRelation->id,
                            'model_id' => $mediaRelation->model_id,
                            'model_type' => $mediaRelation->model_type,
                            'relation_id' => $mediaRelation->relation_id,
                            'related_id' => $mediaRelation->related_id,
                            'related_type' => $mediaRelation->related_type,
                        ]);
                    } catch (Exception $exception) {
                        print 'Exception at: ' . $mediaRelation->id . PHP_EOL;
                        print $exception->getMessage() . PHP_EOL;
                    }

                    print 'Added: ' . $mediaRelation->id . PHP_EOL;
                }
            });

        return Command::SUCCESS;
    }
}
