<?php

namespace App\Console\Commands;

use App\Anime;
use App\AnimeRating;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CalculateAnimeRatings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ratings:calculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculates the average rating for Anime items';

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
     * @return mixed
     */
    public function handle()
    {
        // Mean score of all Anime
        $meanAverageRating = AnimeRating::avg('rating');

        // Start looping through Anime
        $animes = Anime::all();

        foreach($animes as $anime) {
            // Total amount of ratings this Anime has
            $totalRatingCount = AnimeRating::where('anime_id', $anime->id)->count();

            // Check if minimum ratings are acquired
            if($totalRatingCount >= Anime::MINIMUM_RATINGS_REQUIRED) {
                $this->info('=============================');
                $this->info('Calculating for Anime ID ' . $anime->id);

                // Average score for this Anime
                $basicAverageRating = AnimeRating::where('anime_id', $anime->id)->avg('rating');

                // Calculate the weighted rating
                $weightedRating = ($totalRatingCount / ($totalRatingCount + Anime::MINIMUM_RATINGS_REQUIRED)) * $basicAverageRating + (Anime::MINIMUM_RATINGS_REQUIRED / ($totalRatingCount + Anime::MINIMUM_RATINGS_REQUIRED)) * $meanAverageRating;

                $this->info('Calculated weighted rating: '. $weightedRating);
                $this->info('');
            }
            // This Anime does not have enough ratings
            else $this->error('Anime ' . $anime->id . ' does not have enough ratings. (' . $totalRatingCount . '/' . Anime::MINIMUM_RATINGS_REQUIRED . ')');
        }
    }
}
