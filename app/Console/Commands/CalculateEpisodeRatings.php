<?php

namespace App\Console\Commands;

use App\Models\Episode;
use App\Models\MediaRating;
use App\Models\MediaStat;
use DB;
use Illuminate\Console\Command;

class CalculateEpisodeRatings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:episode_ratings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates the average rating for episodes with sufficient data.';

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
        // Mean score of all episodes
        $meanAverageRating = MediaRating::where('model_type', Episode::class)
            ->avg('rating');

        // Get model_id, rating and count per rating
        $mediaRatings = MediaRating::select(['model_id', 'rating', DB::raw('COUNT(*)')])
            ->where('model_type', episode::class)
            ->groupBy(['model_id', 'rating'])
            ->get();

        // Get unique episode id's
        $episodeIDs = $mediaRatings->unique('model_id')
            ->pluck('model_id');

        foreach ($episodeIDs as $episodeID) {
            // Find or create media stat for the episode
            $mediaStat = MediaStat::firstOrCreate([
                'model_type' => Episode::class,
                'model_id' => $episodeID,
            ]);

            // Get all current episode records from media ratings
            $mediaRatingForEpisode = $mediaRatings->where('model_id', '=', $episodeID);

            // Total amount of ratings this Episode has
            $totalRatingCount = $mediaRatingForEpisode->sum('COUNT(*)');

            if ($totalRatingCount >= Episode::MINIMUM_RATINGS_REQUIRED) {
                // Average score for this episode
                $basicAverageRating = $mediaRatingForEpisode->avg('rating');

                // Calculate weighted rating
                $weightedRating = ($totalRatingCount / ($totalRatingCount + Episode::MINIMUM_RATINGS_REQUIRED)) * $basicAverageRating + (Episode::MINIMUM_RATINGS_REQUIRED / ($totalRatingCount + Episode::MINIMUM_RATINGS_REQUIRED)) * $meanAverageRating;

                // Get count of ratings from 0.5 to 5.0
                $rating1 = $mediaRatingForEpisode->where('rating', '=', 0.5);
                $rating2 = $mediaRatingForEpisode->where('rating', '=', 1.0);
                $rating3 = $mediaRatingForEpisode->where('rating', '=', 1.5);
                $rating4 = $mediaRatingForEpisode->where('rating', '=', 2.0);
                $rating5 = $mediaRatingForEpisode->where('rating', '=', 2.5);
                $rating6 = $mediaRatingForEpisode->where('rating', '=', 3.0);
                $rating7 = $mediaRatingForEpisode->where('rating', '=', 3.5);
                $rating8 = $mediaRatingForEpisode->where('rating', '=', 4.0);
                $rating9 = $mediaRatingForEpisode->where('rating', '=', 4.5);
                $rating10 = $mediaRatingForEpisode->where('rating', '=', 5.0);

                // Update media stat
                $mediaStat->update([
                    'rating_1' => $rating1->values()[0]['COUNT(*)'] ?? 0,
                    'rating_2' => $rating2->values()[0]['COUNT(*)'] ?? 0,
                    'rating_3' => $rating3->values()[0]['COUNT(*)'] ?? 0,
                    'rating_4' => $rating4->values()[0]['COUNT(*)'] ?? 0,
                    'rating_5' => $rating5->values()[0]['COUNT(*)'] ?? 0,
                    'rating_6' => $rating6->values()[0]['COUNT(*)'] ?? 0,
                    'rating_7' => $rating7->values()[0]['COUNT(*)'] ?? 0,
                    'rating_8' => $rating8->values()[0]['COUNT(*)'] ?? 0,
                    'rating_9' => $rating9->values()[0]['COUNT(*)'] ?? 0,
                    'rating_10' => $rating10->values()[0]['COUNT(*)'] ?? 0,
                    'rating_average' => $weightedRating,
                    'rating_count' => $totalRatingCount,
                ]);
            }
        }

        return Command::SUCCESS;
    }
}
