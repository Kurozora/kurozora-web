<?php

namespace App\Console\Commands\Calculators;

use App\Models\Anime;
use App\Models\Character;
use App\Models\Episode;
use App\Models\Game;
use App\Models\Manga;
use App\Models\Song;
use App\Models\View;
use DB;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CalculateViews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:views
                            {model : Class name of model to calculate rank}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates the total views of the specified model type.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        DB::disableQueryLog();

        $chunkSize = 50;
        $class = $this->argument('model');

        if ($class === 'all') {
            View::withoutGlobalScopes()
                ->distinct()
                ->select(['viewable_type'])
                ->pluck('viewable_type')
                ->each(function ($modelType) {
                    $this->call('calculate:views', ['model' => $modelType]);
                    $this->newLine();
                });

            return Command::SUCCESS;
        }

        $this->info('Calculating views for: ' . $class);

        $totalCount = View::select(['viewable_type', 'viewable_id'])
            ->where('viewable_type', '=', $class)
            ->distinct(['viewable_type', 'viewable_id']) // Method takes in a parameter using `func_get_args()`
            ->count();
        $bar = $this->output->createProgressBar($totalCount);

        View::select(['viewable_type', 'viewable_id', DB::raw('COUNT(*) as views_count')])
            ->where('viewable_type', '=', $class)
            ->groupBy('viewable_type', 'viewable_id')
            ->with(['viewable' => function (MorphTo $morphTo) {
                $morphTo->constrain([
                    Anime::class => function (Builder $query) {
                        $query->with(['mediaStat']);
                    },
                    Character::class => function (Builder $query) {
                        $query->with(['mediaStat']);
                    },
                    Episode::class => function (Builder $query) {
                        $query->with(['mediaStat']);
                    },
                    Game::class => function (Builder $query) {
                        $query->with(['mediaStat']);
                    },
                    Manga::class => function (Builder $query) {
                        $query->with(['mediaStat']);
                    },
                    Song::class => function (Builder $query) {
                        $query->with(['mediaStat']);
                    },
                ]);
            }])
            ->chunkById($chunkSize, function (Collection $views) use ($class, $bar) {
                DB::transaction(function () use ($class, $bar, $views) {
                    $views->each(function (View $view) use ($bar) {
                        $model = $view->viewable;

                        if (!empty($model)) {
                            // Update the view_count property
                            $model->updateQuietly([
                                'view_count' => $model->view_count + $view->views_count,
                            ]);
                        }

                        $bar->advance();
                    });

                    // Delete the calculated views
                    $viewableIDs = $views->pluck('viewable_id')
                        ->toArray();

                    View::where('viewable_type', '=', $class)
                        ->whereIn('viewable_id', $viewableIDs)
                        ->forceDelete();
                });
            }, 'viewable_id');

        $bar->finish();

        DB::enableQueryLog();

        return Command::SUCCESS;
    }
}
