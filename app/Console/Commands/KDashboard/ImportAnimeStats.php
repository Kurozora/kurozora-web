<?php

namespace App\Console\Commands\KDashboard;

use App\Models\Anime;
use App\Models\KDashboard\Stats as KStats;
use App\Models\MediaRating;
use Illuminate\Console\Command;

class ImportAnimeStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:kdashboard_anime_stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports anime stats from KDashboard database.';

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
        $meanAverageRating = MediaRating::avg('rating');

        $statsCount = KStats::where('type', 'anime')->count();

        $progressBar = $this->output->createProgressBar($statsCount);
        $progressBar->start();

        KStats::where('type', 'anime')->chunk(5000, function ($stats) use ($progressBar, $meanAverageRating) {
            foreach ($stats as $stat) {
                if ($stat->type == 'anime') {
                    $anime = Anime::firstWhere('mal_id', $stat->id);

                    if (!empty($anime)) {
                        // Total amount of ratings this Anime has
                        $totalRatingCount = (
                            $stat->score_1 +
                            $stat->score_2 +
                            $stat->score_3 +
                            $stat->score_4 +
                            $stat->score_5 +
                            $stat->score_6 +
                            $stat->score_7 +
                            $stat->score_8 +
                            $stat->score_9 +
                            $stat->score_10
                        );

                        if ($totalRatingCount === 0) {
                            // Average score for this Anime
                            $basicAverageRating = 0;
                        } else {
                            // Average score for this Anime
                            $basicAverageRating = (
                                    $stat->score_1 * 0.5 +
                                    $stat->score_2 * 1.0 +
                                    $stat->score_3 * 1.5 +
                                    $stat->score_4 * 2.0 +
                                    $stat->score_5 * 2.5 +
                                    $stat->score_6 * 3.0 +
                                    $stat->score_7 * 3.5 +
                                    $stat->score_8 * 4.0 +
                                    $stat->score_9 * 4.5 +
                                    $stat->score_10 * 5.0
                                ) / $totalRatingCount;
                        }

                        // Calculate weighted rating
                        $weightedRating = ($totalRatingCount / ($totalRatingCount + Anime::MINIMUM_RATINGS_REQUIRED)) * $basicAverageRating + (Anime::MINIMUM_RATINGS_REQUIRED / ($totalRatingCount + Anime::MINIMUM_RATINGS_REQUIRED)) * $meanAverageRating;

                        $anime->stats()->updateOrCreate([
                            'model_type' => Anime::class,
                            'model_id' => $anime->id
                        ], [
                            'rating_1' => $stat->score_1,
                            'rating_2' => $stat->score_2,
                            'rating_3' => $stat->score_3,
                            'rating_4' => $stat->score_4,
                            'rating_5' => $stat->score_5,
                            'rating_6' => $stat->score_6,
                            'rating_7' => $stat->score_7,
                            'rating_8' => $stat->score_8,
                            'rating_9' => $stat->score_9,
                            'rating_10' => $stat->score_10,
                            'rating_average' => $weightedRating,
                            'rating_count' => $totalRatingCount,
                        ]);
                    } else {
                        $this->info('Anime not found id: ' . $stat->id);
                    }

                    $progressBar->setProgress($progressBar->getProgress() + 1);
                }
            }
        });

        $progressBar->finish();

        return Command::SUCCESS;
    }
}
