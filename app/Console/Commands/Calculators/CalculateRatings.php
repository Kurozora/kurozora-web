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
use Laravel\Telescope\Telescope;
use Pulse;

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
        Pulse::stopRecording();
        Telescope::stopRecording();

        $chunkSize = 2000;
        $class = $this->argument('model');

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

        // Mean rating across all instances of this model class — corpus prior for the Bayesian formula.
        $meanRating = MediaRating::withoutGlobalScopes()
            ->where('model_type', '=', $class)
            ->avg('rating');

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
            ->withAvg('mediaRatings', 'rating')
            ->chunkById($chunkSize, function (Collection $models) use ($class, $meanRating, $bar) {
                DB::transaction(function () use ($class, $models, $meanRating, $bar) {
                    $minimum = $class::minimumRatingsRequired();

                    $rows = $models->map(function ($model) use ($minimum, $meanRating) {
                        $totalRatingCount = $model->rating_0_5_count
                            + $model->rating_1_0_count + $model->rating_1_5_count
                            + $model->rating_2_0_count + $model->rating_2_5_count
                            + $model->rating_3_0_count + $model->rating_3_5_count
                            + $model->rating_4_0_count + $model->rating_4_5_count
                            + $model->rating_5_0_count;

                        $basicAverageRating = $model->media_ratings_avg_rating;
                        $weightedRating = ($totalRatingCount / ($totalRatingCount + $minimum)) * $basicAverageRating
                            + ($minimum / ($totalRatingCount + $minimum)) * $meanRating;

                        return [
                            'model_type' => $model->getMorphClass(),
                            'model_id' => $model->id,
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
                        ];
                    })->all();

                    MediaStat::upsert($rows, ['model_type', 'model_id'], [
                        'rating_1', 'rating_2', 'rating_3', 'rating_4', 'rating_5',
                        'rating_6', 'rating_7', 'rating_8', 'rating_9', 'rating_10',
                        'rating_average', 'rating_count',
                    ]);

                    $bar->advance($models->count());
                });
            });

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }
}
