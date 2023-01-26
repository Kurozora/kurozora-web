<?php

namespace App\Console\Commands\ELB;

use App\Models\MediaStat;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportMediaStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:elb_media_stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports media stats from ELB database.';

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
        MediaStat::on('elb')
            ->orderBy('id')
            ->chunk(1000, function (Collection $mediaStats) {
                /** @var MediaStat $mediaStat */
                foreach ($mediaStats as $mediaStat) {
                    try {
                        MediaStat::updateOrCreate([
                            'model_id' => $mediaStat->model_id,
                            'model_type' => $mediaStat->model_type,
                        ], [
                            'id' => $mediaStat->id,
                            'model_id' => $mediaStat->model_id,
                            'model_type' => $mediaStat->model_type,
                            'model_count' => $mediaStat->model_count,
                            'planning_count' => $mediaStat->planning_count,
                            'in_progress_count' => $mediaStat->in_progress_count,
                            'completed_count' => $mediaStat->completed_count,
                            'on_hold_count' => $mediaStat->on_hold_count,
                            'dropped_count' => $mediaStat->dropped_count,
                            'rating_1' => $mediaStat->rating_1,
                            'rating_2' => $mediaStat->rating_2,
                            'rating_3' => $mediaStat->rating_3,
                            'rating_4' => $mediaStat->rating_4,
                            'rating_5' => $mediaStat->rating_5,
                            'rating_6' => $mediaStat->rating_6,
                            'rating_7' => $mediaStat->rating_7,
                            'rating_8' => $mediaStat->rating_8,
                            'rating_9' => $mediaStat->rating_9,
                            'rating_10' => $mediaStat->rating_10,
                            'rating_average' => $mediaStat->rating_average,
                            'rating_count' => $mediaStat->rating_count,
                        ]);
                    } catch (Exception $exception) {
                        print 'Exception at: ' . $mediaStat->id . PHP_EOL;
                        print $exception->getMessage() . PHP_EOL;
                    }

                    print 'Added: ' . $mediaStat->id . PHP_EOL;
                }
            });

        return Command::SUCCESS;
    }
}
