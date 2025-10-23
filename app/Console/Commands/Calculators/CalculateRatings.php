<?php

namespace App\Console\Commands\Calculators;

use App\Models\Anime;
use App\Models\Character;
use App\Models\Episode;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MediaRating;
use App\Models\MediaStat;
use App\Models\Person;
use App\Models\Song;
use App\Models\Studio;
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
        $meanRating = MediaRating::withoutGlobalScopes()
            ->where('model_type', '=', $class)
            ->avg('rating');

        if ($class === 'all') {
            MediaStat::withoutGlobalScopes()
                ->distinct()
                ->select(['model_type'])
                ->pluck('model_type')
                ->each(function ($modelType) {
                    $this->call('calculate:ratings', ['model' => $modelType]);
                    $this->newLine();
                });

            return Command::SUCCESS;
        }

        if ($class::minimumRatingsRequired() == 999999999) {
            $this->warn('Calculating ' . $class . ' ratings is blocked for now.');
            return Command::SUCCESS;
        }

        // Get unique Model IDs
        $model = (match ($class) {
            Anime::class => Anime::withoutGlobalScopes(),
            Character::class => Character::withoutGlobalScopes(),
            Episode::class => Episode::withoutGlobalScopes(),
            Game::class => Game::withoutGlobalScopes(),
            Manga::class => Manga::withoutGlobalScopes(),
            Person::class => Person::withoutGlobalScopes(),
            Song::class => Song::withoutGlobalScopes(),
            Studio::class => Studio::withoutGlobalScopes(),
            default => null
        })->whereHas('mediaRatings');

        if (empty($model)) {
            $this->error('Unsupported model.');
            return Command::FAILURE;
        }

        $this->info('Calculating ratings for: ' . $class);

        $modelsInLibraryCount = $model->count();
        $bar = $this->output->createProgressBar($modelsInLibraryCount);

        $model
            ->withCount([
                'mediaRatings as rating_0_5_count' => function ($query) {
                    $query->where('rating', '=', 0.5);
                },
                'mediaRatings as rating_1_0_count' => function ($query) {
                    $query->where('rating', '=', 1.0);
                },
                'mediaRatings as rating_1_5_count' => function ($query) {
                    $query->where('rating', '=', 1.5);
                },
                'mediaRatings as rating_2_0_count' => function ($query) {
                    $query->where('rating', '=', 2.0);
                },
                'mediaRatings as rating_2_5_count' => function ($query) {
                    $query->where('rating', '=', 2.5);
                },
                'mediaRatings as rating_3_0_count' => function ($query) {
                    $query->where('rating', '=', 3.0);
                },
                'mediaRatings as rating_3_5_count' => function ($query) {
                    $query->where('rating', '=', 3.5);
                },
                'mediaRatings as rating_4_0_count' => function ($query) {
                    $query->where('rating', '=', 4.0);
                },
                'mediaRatings as rating_4_5_count' => function ($query) {
                    $query->where('rating', '=', 4.5);
                },
                'mediaRatings as rating_5_0_count' => function ($query) {
                    $query->where('rating', '=', 5.0);
                }
            ])
            ->with([
                'mediaStat'
            ])
            ->withAvg('mediaRatings', 'rating')
            ->chunkById($chunkSize, function (Collection $models) use ($class, $meanRating, $bar) {
                DB::transaction(function () use ($class, $models, $meanRating, $bar) {
                    $models->each(function ($model) use ($class, $meanRating, $bar) {
                        // Find or create media stat for the model
                        $mediaStat = $model->mediaStat;

                        if (empty($mediaStat)) {
                            $mediaStat = MediaStat::create([
                                'model_type' => $model->getMorphClass(),
                                'model_id' => $model->id,
                            ]);
                        }

                        // Total amount of ratings this Model has
                        $totalRatingCount = $model->rating_0_5_count
                            + $model->rating_1_0_count + $model->rating_1_5_count
                            + $model->rating_2_0_count + $model->rating_2_5_count
                            + $model->rating_3_0_count + $model->rating_3_5_count
                            + $model->rating_4_0_count + $model->rating_4_5_count
                            + $model->rating_5_0_count;

                        // Average score for this Model
                        $basicAverageRating = $model->media_ratings_avg_rating;

                        // Calculate weighted rating
                        $weightedRating = ($totalRatingCount / ($totalRatingCount + $class::minimumRatingsRequired())) * $basicAverageRating
                            + ($class::minimumRatingsRequired() / ($totalRatingCount + $class::minimumRatingsRequired())) * $meanRating;

                        // Update media stat
                        $mediaStat->updateQuietly([
                            'rating_1' => $model->rating_0_5_count,
                            'rating_2' => $model->rating_1_0_count,
                            'rating_3' => $model->rating_1_5_count,
                            'rating_4' => $model->rating_2_0_count,
                            'rating_5' => $model->rating_2_5_count,
                            'rating_6' => $model->rating_3_0_count,
                            'rating_7' => $model->rating_3_5_count,
                            'rating_8' => $model->rating_4_0_count,
                            'rating_9' => $model->rating_4_5_count,
                            'rating_10' => $model->rating_5_0_count,
                            'rating_average' => $weightedRating,
                            'rating_count' => $totalRatingCount,
                        ]);

                        $bar->advance();
                    });
                });
            });

        return Command::SUCCESS;
    }
}
