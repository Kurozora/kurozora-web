<?php

namespace App\Console\Commands\ELB;

use App\Models\MediaRating;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportMediaRatings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:elb_media_ratings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports media ratings from the ELB database.';

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
        MediaRating::on('elb')
            ->orderBy('id')
            ->chunk(1000, function (Collection $mediaRatings) {
                /** @var MediaRating $mediaRating */
                foreach ($mediaRatings as $mediaRating) {
                    try {
                        MediaRating::updateOrCreate([
                            'user_id' => $mediaRating->user_id,
                            'model_id' => $mediaRating->model_id,
                            'model_type' => $mediaRating->model_type,
                        ], [
                            'id' => $mediaRating->id,
                            'user_id' => $mediaRating->user_id,
                            'model_id' => $mediaRating->model_id,
                            'model_type' => $mediaRating->model_type,
                            'rating' => $mediaRating->rating,
                            'description' => $mediaRating->description,
                        ]);
                    } catch (Exception $exception) {
                        print 'Exception at: ' . $mediaRating->id . PHP_EOL;
                        print $exception->getMessage() . PHP_EOL;
                    }

                    print 'Added: ' . $mediaRating->id . PHP_EOL;
                }
            });

        return Command::SUCCESS;
    }
}
