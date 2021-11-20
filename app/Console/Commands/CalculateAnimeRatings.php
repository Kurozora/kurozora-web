<?php

namespace App\Console\Commands;

use App\Models\Anime;
use App\Models\AnimeRating;
use App\Models\MediaStat;
use DB;
use Illuminate\Console\Command;

class CalculateAnimeRatings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:anime_ratings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates the average rating for anime with sufficient data.';

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
        // Mean score of all Anime
        $meanAverageRating = AnimeRating::avg('rating');

        // Get anime_id, rating and count per rating
        $animeRatings = AnimeRating::select(['anime_id', 'rating', DB::raw('COUNT(*)')])
            ->groupBy(['anime_id', 'rating'])
            ->get();

        // Get unique anime id's
        $animeIDs = $animeRatings->unique('anime_id')
            ->pluck('anime_id');

        foreach ($animeIDs as $animeID) {
            // Find or create media stat for the anime
            $mediaStat = MediaStat::firstOrCreate([
                'model_type' => Anime::class,
                'model_id' => $animeID,
            ]);

            // Get all current anime records from anime ratings
            $animeRatingForAnime = $animeRatings->where('anime_id', '=', $animeID);

            // Total amount of ratings this Anime has
            $totalRatingCount = $animeRatingForAnime->sum('COUNT(*)');

            if ($totalRatingCount >= Anime::MINIMUM_RATINGS_REQUIRED) {
                // Average score for this Anime
                $basicAverageRating = $animeRatingForAnime->avg('rating');

                // Calculate weighted rating
                $weightedRating = ($totalRatingCount / ($totalRatingCount + Anime::MINIMUM_RATINGS_REQUIRED)) * $basicAverageRating + (Anime::MINIMUM_RATINGS_REQUIRED / ($totalRatingCount + Anime::MINIMUM_RATINGS_REQUIRED)) * $meanAverageRating;

                // Get count of ratings from 0.5 to 5.0
                $rating1 = $animeRatingForAnime->where('rating', '=', 0.5);
                $rating2 = $animeRatingForAnime->where('rating', '=', 1.0);
                $rating3 = $animeRatingForAnime->where('rating', '=', 1.5);
                $rating4 = $animeRatingForAnime->where('rating', '=', 2.0);
                $rating5 = $animeRatingForAnime->where('rating', '=', 2.5);
                $rating6 = $animeRatingForAnime->where('rating', '=', 3.0);
                $rating7 = $animeRatingForAnime->where('rating', '=', 3.5);
                $rating8 = $animeRatingForAnime->where('rating', '=', 4.0);
                $rating9 = $animeRatingForAnime->where('rating', '=', 4.5);
                $rating_10 = $animeRatingForAnime->where('rating', '=', 5.0);

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
                    'rating_10' => $rating_10->values()[0]['COUNT(*)'] ?? 0,
                    'rating_average' => $weightedRating,
                    'rating_count' => $totalRatingCount,
                ]);
            }
        }

        return Command::SUCCESS;
    }
}
