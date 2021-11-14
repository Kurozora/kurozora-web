<?php

namespace App\Console\Commands;

use App\Models\Anime;
use App\Models\AnimeRating;
use App\Scopes\TvRatingScope;
use Illuminate\Console\Command;

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
     * @return int
     */
    public function handle(): int
    {
        // Mean score of all Anime
        $meanAverageRating = AnimeRating::avg('rating');

        // Start looping through Anime
        $animes = Anime::withoutGlobalScope(new TvRatingScope)->get();

        foreach ($animes as $anime) {
            // Total amount of ratings this Anime has
            $totalRatingCount = $anime->ratings()->count();

            // Check if minimum ratings are acquired
            if ($totalRatingCount >= Anime::MINIMUM_RATINGS_REQUIRED) {
                $this->info('=============================');
                $this->info('Calculating for Anime ID ' . $anime->id);

                // Average score for this Anime
                $basicAverageRating = $anime->ratings()->avg('rating');

                // Calculate the weighted rating
                $weightedRating = ($totalRatingCount / ($totalRatingCount + Anime::MINIMUM_RATINGS_REQUIRED)) * $basicAverageRating + (Anime::MINIMUM_RATINGS_REQUIRED / ($totalRatingCount + Anime::MINIMUM_RATINGS_REQUIRED)) * $meanAverageRating;

                $this->info('Calculated weighted rating: '. $weightedRating);
                $this->info('');
            } else { // This Anime does not have enough ratings
                $this->error('Anime ' . $anime->id . ' does not have enough ratings. (' . $totalRatingCount . '/' . Anime::MINIMUM_RATINGS_REQUIRED . ')');
            }
        }

        return Command::SUCCESS;
    }
}
