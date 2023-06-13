<?php

namespace App\Console\Commands\Calculators;

use App\Models\MediaRating;
use App\Models\MediaStat;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class CalculateRatings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:ratings
                            {model : Class name of model to calculate rank}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates the average rating of the specified model type.';

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
        $chunkSize = 1000;
        $class = $this->argument('model');

        // Mean score of all Model
        $meanRating = MediaRating::where('model_type', $class)
            ->avg('rating');

        // Get model_id, rating and count per rating
        $mediaRatings = MediaRating::select(['model_id', 'rating', DB::raw('COUNT(*) as total')])
            ->where('model_type', $class)
            ->groupBy(['model_id', 'rating'])
            ->get();

        // Get unique Model IDs
        $modelIDs = $mediaRatings->pluck('model_id')->unique();

        // Loop through chunks
        $modelIDs->chunk($chunkSize)
            ->each(function (Collection $chunk) use ($mediaRatings, $class, $meanRating) {
                $existingMediaStats = MediaStat::whereIn('model_id', $chunk)
                    ->get()
                    ->keyBy('model_id');

                $updates = [];
                $newMediaStats = [];

                foreach ($chunk as $modelID) {
                    $mediaStat = $existingMediaStats->get($modelID);

                    // If MediaStat doesn't exist for this Model, create a new one.
                    if (!$mediaStat) {
                        $mediaStat = new MediaStat([
                            'model_type' => $class,
                            'model_id' => $modelID,
                        ]);
                        $newMediaStats[$modelID] = $mediaStat;
                    }

                    // Get all current Model records from MediaRating
                    $mediaRatingForModel = $mediaRatings->where('model_id', $modelID);

                    // Total amount of ratings this Model has
                    $totalRatingCount = $mediaRatingForModel->sum('total');

                    if ($totalRatingCount >= $class::MINIMUM_RATINGS_REQUIRED) {
                        // Average score for this Model
                        $basicAverageRating = $mediaRatingForModel->avg('rating');

                        // Calculate weighted rating
                        $weightedRating = ($totalRatingCount / ($totalRatingCount + $class::MINIMUM_RATINGS_REQUIRED)) * $basicAverageRating + ($class::MINIMUM_RATINGS_REQUIRED / ($totalRatingCount + $class::MINIMUM_RATINGS_REQUIRED)) * $meanRating;

                        // Get count of ratings from 0.5 to 5.0
                        $ratingCounts = $mediaRatingForModel->whereIn('rating', [0.5, 1.0, 1.5, 2.0, 2.5, 3.0, 3.5, 4.0, 4.5, 5.0])
                            ->pluck('total', 'rating')
                            ->toArray();

                        // Update media stat
                        $updates[$modelID] = [
                            'rating_1' => $ratingCounts[0.5] ?? 0,
                            'rating_2' => $ratingCounts[1.0] ?? 0,
                            'rating_3' => $ratingCounts[1.5] ?? 0,
                            'rating_4' => $ratingCounts[2.0] ?? 0,
                            'rating_5' => $ratingCounts[2.5] ?? 0,
                            'rating_6' => $ratingCounts[3.0] ?? 0,
                            'rating_7' => $ratingCounts[3.5] ?? 0,
                            'rating_8' => $ratingCounts[4.0] ?? 0,
                            'rating_9' => $ratingCounts[4.5] ?? 0,
                            'rating_10' => $ratingCounts[5.0] ?? 0,
                            'rating_average' => $weightedRating,
                            'rating_count' => $totalRatingCount,
                        ];
                    }
                }

                // Create MediaStat for the Model
                if (!empty($newMediaStats)) {
                    MediaStat::insert($newMediaStats);
                }

                // Update the MediaStat of the Model
                if (!empty($updates)) {
                    MediaStat::whereIn('model_id', array_keys($updates))
                        ->update($updates);
                }
            });

        return Command::SUCCESS;
    }
}
