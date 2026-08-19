<?php

namespace App\Console\Commands\Calculators;

use App\Enums\FavoriteKind;
use App\Models\Anime;
use App\Models\Character;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MediaRating;
use App\Models\MediaStat;
use App\Models\Person;
use App\Models\Song;
use App\Models\Studio;
use App\Models\UserFavorite;
use DB;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Laravel\Telescope\Telescope;
use Pulse;

class CalculateFavorites extends Command
{
    // Sentiment gap boundaries
    const float MAXIMUM_SENTIMENT_GAP = 1.0;
    const int CONFIDENCE_PRIOR = 25;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:favorites
                            {model : Class name of model to calculate favorites for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates the favorite counts of the specified model type.';

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
                    // Model types outside `FavoriteKind` have no favorites to count.
                    if ($this->favorableQuery($modelType) === null) {
                        return;
                    }

                    $this->call('calculate:favorites', ['model' => $modelType]);
                    $this->newLine();
                });

            return Command::SUCCESS;
        }

        $model = $this->favorableQuery($class);

        if (empty($model)) {
            $this->error('Unsupported model.');
            return Command::FAILURE;
        }

        $this->info('Calculating favorites for: ' . $class);

        $table = $model->getModel()->getTable();
        $morphClass = $model->getModel()->getMorphClass();

        $modelsInLibraryCount = $model->count();
        $bar = $this->output->createProgressBar($modelsInLibraryCount);

        $model
            ->addSelect([
                'favorite_count' => UserFavorite::whereColumn(UserFavorite::TABLE_NAME . '.favorable_id', '=', $table . '.id')
                    ->where(UserFavorite::TABLE_NAME . '.favorable_type', '=', $morphClass)
                    ->selectRaw('count(*)'),
                // Ratings left here, which is the population the favorite share is taken from.
                'rater_count' => MediaRating::whereColumn(MediaRating::TABLE_NAME . '.model_id', '=', $table . '.id')
                    ->where(MediaRating::TABLE_NAME . '.model_type', '=', $morphClass)
                    ->selectRaw('count(*)'),
                // Favorites left by users who also rated the same model — the favorite share numerator.
                'rater_favorite_count' => UserFavorite::whereColumn(UserFavorite::TABLE_NAME . '.favorable_id', '=', $table . '.id')
                    ->where(UserFavorite::TABLE_NAME . '.favorable_type', '=', $morphClass)
                    ->whereExists(
                        MediaRating::whereColumn(MediaRating::TABLE_NAME . '.user_id', '=', UserFavorite::TABLE_NAME . '.user_id')
                            ->whereColumn(MediaRating::TABLE_NAME . '.model_id', '=', $table . '.id')
                            ->where(MediaRating::TABLE_NAME . '.model_type', '=', $morphClass)
                    )
                    ->selectRaw('count(*)'),
            ])
            ->chunkById($chunkSize, function (Collection $models) use ($bar) {
                DB::transaction(function () use ($models, $bar) {
                    $rows = $models->map(function ($model) {
                        return [
                            'model_type' => $model->getMorphClass(),
                            'model_id' => $model->id,
                            'rater_count' => $model->rater_count,
                            'favorite_count' => $model->favorite_count,
                            'rater_favorite_count' => $model->rater_favorite_count,
                        ];
                    })->all();

                    MediaStat::upsert($rows, ['model_type', 'model_id'], [
                        'rater_count', 'favorite_count', 'rater_favorite_count',
                    ]);

                    $bar->advance($models->count());
                });
            });

        $this->calculateSentimentGap($morphClass);

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }

    /**
     * Writes how far each model's affection stands from its score, in percentiles.
     *
     * @param string $morphClass
     *
     * @return void
     */
    private function calculateSentimentGap(string $morphClass): void
    {
        $audience = FavoriteKind::fromMorphClass($morphClass)?->isLibraryTrackable()
            ? 'model_count'
            : 'rater_count';

        $this->newLine();
        $this->info('Calculating sentiment gaps for: ' . $morphClass);

        // Standings, not deviations. A share and a star average are shaped differently, so
        // the same rank on each yields a different deviation, and a model at the top of both
        // would read as loved far beyond its score. Comparing standings removes the shape.
        $rankable = MediaStat::withoutGlobalScopes()
            ->where('model_type', '=', $morphClass)
            ->where($audience, '>=', MediaStat::MINIMUM_FAVORITE_SAMPLE)
            ->select(['model_type', 'model_id', 'rating_average', 'model_count', 'rater_count', 'favorite_count', 'rater_favorite_count'])
            ->get();

        $sortedShares = $rankable->map(fn (MediaStat $mediaStat) => $mediaStat->favorite_share)->sort()->values()->all();
        $sortedRatings = $rankable->map(fn (MediaStat $mediaStat) => (float) $mediaStat->rating_average)->sort()->values()->all();

        $bar = $this->output->createProgressBar($rankable->count());

        $rankable->chunk(2000)->each(function (Collection $mediaStats) use ($bar, $sortedShares, $sortedRatings) {
            DB::transaction(function () use ($mediaStats, $bar, $sortedShares, $sortedRatings) {
                $rows = $mediaStats->map(function (MediaStat $mediaStat) use ($sortedShares, $sortedRatings) {
                    $favoriteStanding = $this->standing($sortedShares, $mediaStat->favorite_share);
                    $ratingStanding = $this->standing($sortedRatings, (float) $mediaStat->rating_average);

                    // Confidence: is the audience substantial enough to be trusted?
                    $confidence = $mediaStat->favorite_audience / ($mediaStat->favorite_audience + self::CONFIDENCE_PRIOR);
                    $gap = ($favoriteStanding - $ratingStanding) * $confidence;

                    return [
                        'model_type' => $mediaStat->model_type,
                        'model_id' => $mediaStat->model_id,
                        'sentiment_gap' => round(max(-self::MAXIMUM_SENTIMENT_GAP, min(self::MAXIMUM_SENTIMENT_GAP, $gap)), 4),
                    ];
                })->all();

                MediaStat::upsert($rows, ['model_type', 'model_id'], ['sentiment_gap']);

                $bar->advance($mediaStats->count());
            });
        });

        // Models below the gate carry no standing to compare.
        MediaStat::withoutGlobalScopes()
            ->where('model_type', '=', $morphClass)
            ->where($audience, '<', MediaStat::MINIMUM_FAVORITE_SAMPLE)
            ->toBase()
            ->update(['sentiment_gap' => 0]);

        $bar->finish();
        $this->newLine();
    }

    /**
     * Returns the portion of the given sorted values that the given value stands above.
     *
     * @param array $sortedValues
     * @param float $value
     *
     * @return float
     */
    private function standing(array $sortedValues, float $value): float
    {
        $low = 0;
        $high = count($sortedValues);

        while ($low < $high) {
            $middle = intdiv($low + $high, 2);

            if ($sortedValues[$middle] < $value) {
                $low = $middle + 1;
            } else {
                $high = $middle;
            }
        }

        return $low / max(count($sortedValues) - 1, 1);
    }

    /**
     * Returns the unscoped query of the given favorable class.
     *
     * @param string $class
     *
     * @return Builder|null
     */
    private function favorableQuery(string $class): ?Builder
    {
        return match ($class) {
            Anime::class => Anime::withoutGlobalScopes(),
            Character::class => Character::withoutGlobalScopes(),
            Game::class => Game::withoutGlobalScopes(),
            Manga::class => Manga::withoutGlobalScopes(),
            Person::class => Person::withoutGlobalScopes(),
            Song::class => Song::withoutGlobalScopes(),
            Studio::class => Studio::withoutGlobalScopes(),
            default => null
        };
    }
}
