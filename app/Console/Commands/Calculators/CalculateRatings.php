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
                    /** @var ?MediaStat $mediaStat */
                    $mediaStat = $existingMediaStats->get($modelID);
                    $isNew = false;

                    // If MediaStat doesn't exist for this Model, create a new one.
                    if (!$mediaStat) {
                        $mediaStat = (new MediaStat([
                            'model_type' => $class,
                            'model_id' => $modelID,
                        ]));
                        $isNew = true;
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
                        $ratingCounts = $mediaRatingForModel->whereIn('rating', ['0.5', '1.0', '1.5', '2.0', '2.5', '3.0', '3.5', '4.0', '4.5', '5.0'])
                            ->mapWithKeys(function ($rating) {
                                // Convert 0.5...5 to 1...10
                                return [($rating->rating * 2) => $rating->total];
                            })
                            ->toArray();

                        // Update media stat
                        $attributes = [
                            'model_id' => $modelID,
                            'model_type' => $class,
                            'rating_1' => $ratingCounts[1] ?? 0,
                            'rating_2' => $ratingCounts[2] ?? 0,
                            'rating_3' => $ratingCounts[3] ?? 0,
                            'rating_4' => $ratingCounts[4] ?? 0,
                            'rating_5' => $ratingCounts[5] ?? 0,
                            'rating_6' => $ratingCounts[6] ?? 0,
                            'rating_7' => $ratingCounts[7] ?? 0,
                            'rating_8' => $ratingCounts[8] ?? 0,
                            'rating_9' => $ratingCounts[9] ?? 0,
                            'rating_10' => $ratingCounts[10] ?? 0,
                            'rating_average' => $weightedRating,
                            'rating_count' => $totalRatingCount,
                        ];

                        if ($isNew) {
                            $mediaStat->fill($attributes);
                            $newMediaStats[$modelID] = $mediaStat->toArray();
                        } else {
                            $updates[] = $attributes;
                        }
                    }
                }

                // Create MediaStat for the Model
                if (!empty($newMediaStats)) {
                    MediaStat::upsert($newMediaStats, ['model_type', 'model_id']);
                }

                // Update the MediaStat of the Model
                if (!empty($updates)) {
                    MediaStat::upsert($updates, ['model_type', 'model_id']);
                }
            });

        return Command::SUCCESS;
    }
}
