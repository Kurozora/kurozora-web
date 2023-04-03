<?php

namespace App\Console\Commands\Calculators;

use App\Models\Game;
use App\Models\MediaRating;
use App\Models\MediaStat;
use DB;
use Illuminate\Console\Command;

class CalculateGameRatings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:game_ratings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates the average rating for game with sufficient data.';

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
        // Mean score of all Game
        $meanAverageRating = MediaRating::where('model_type', Game::class)
            ->avg('rating');

        // Get model_id, rating and count per rating
        $gameRatings = MediaRating::select(['model_id', 'rating', DB::raw('COUNT(*) as total')])
            ->where('model_type', Game::class)
            ->groupBy(['model_id', 'rating'])
            ->get();

        // Get unique game id's
        $gameIDs = $gameRatings->unique('model_id')
            ->pluck('model_id');

        foreach ($gameIDs as $gameID) {
            // Find or create media stat for the game
            $mediaStat = MediaStat::firstOrCreate([
                'model_type' => Game::class,
                'model_id' => $gameID,
            ]);

            // Get all current game records from game ratings
            $gameRatingForGame = $gameRatings->where('model_id', '=', $gameID);

            // Total amount of ratings this Game has
            $totalRatingCount = $gameRatingForGame->sum('total');

            if ($totalRatingCount >= Game::MINIMUM_RATINGS_REQUIRED) {
                // Average score for this Game
                $basicAverageRating = $gameRatingForGame->avg('rating');

                // Calculate weighted rating
                $weightedRating = ($totalRatingCount / ($totalRatingCount + Game::MINIMUM_RATINGS_REQUIRED)) * $basicAverageRating + (Game::MINIMUM_RATINGS_REQUIRED / ($totalRatingCount + Game::MINIMUM_RATINGS_REQUIRED)) * $meanAverageRating;

                // Get count of ratings from 0.5 to 5.0
                $rating1 = $gameRatingForGame->where('rating', '=', 0.5);
                $rating2 = $gameRatingForGame->where('rating', '=', 1.0);
                $rating3 = $gameRatingForGame->where('rating', '=', 1.5);
                $rating4 = $gameRatingForGame->where('rating', '=', 2.0);
                $rating5 = $gameRatingForGame->where('rating', '=', 2.5);
                $rating6 = $gameRatingForGame->where('rating', '=', 3.0);
                $rating7 = $gameRatingForGame->where('rating', '=', 3.5);
                $rating8 = $gameRatingForGame->where('rating', '=', 4.0);
                $rating9 = $gameRatingForGame->where('rating', '=', 4.5);
                $rating10 = $gameRatingForGame->where('rating', '=', 5.0);

                // Update media stat
                $mediaStat->update([
                    'rating_1' => $rating1->values()[0]['total'] ?? 0,
                    'rating_2' => $rating2->values()[0]['total'] ?? 0,
                    'rating_3' => $rating3->values()[0]['total'] ?? 0,
                    'rating_4' => $rating4->values()[0]['total'] ?? 0,
                    'rating_5' => $rating5->values()[0]['total'] ?? 0,
                    'rating_6' => $rating6->values()[0]['total'] ?? 0,
                    'rating_7' => $rating7->values()[0]['total'] ?? 0,
                    'rating_8' => $rating8->values()[0]['total'] ?? 0,
                    'rating_9' => $rating9->values()[0]['total'] ?? 0,
                    'rating_10' => $rating10->values()[0]['total'] ?? 0,
                    'rating_average' => $weightedRating,
                    'rating_count' => $totalRatingCount,
                ]);
            }
        }

        return Command::SUCCESS;
    }
}
