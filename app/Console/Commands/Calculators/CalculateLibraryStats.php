<?php

namespace App\Console\Commands\Calculators;

use App\Enums\UserLibraryStatus;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MediaStat;
use App\Models\UserLibrary;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class CalculateLibraryStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:library_stats
                            {model : Class name of model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate library stats for the specified model.';

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
        DB::disableQueryLog();

        $chunkSize = 100;
        $class = $this->argument('model');

        if ($class === 'all') {
            UserLibrary::withoutGlobalScopes()
                ->distinct()
                ->select(['trackable_type'])
                ->pluck('trackable_type')
                ->each(function ($modelType) {
                    $this->call('calculate:library_stats', ['model' => $modelType]);
                    $this->newLine();
                });

            return Command::SUCCESS;
        }

        $model = match ($class) {
            Anime::class => Anime::withoutGlobalScopes(),
            Manga::class => Manga::withoutGlobalScopes(),
            Game::class => Game::withoutGlobalScopes(),
            default => null
        };

        if (empty($model)) {
            $this->error('Unsupported model.');
            return Command::FAILURE;
        }

        $this->info('Calculating stats for: ' . $class);

        $modelsInLibraryCount = $model->whereHas('library')
            ->count();
        $bar = $this->output->createProgressBar($modelsInLibraryCount);

        $model->whereHas('library')
            ->with([
                'mediaStat'
            ])
            ->withCount([
                'library as in_progress_count' => function ($query) {
                    $query->where('status', '=', UserLibraryStatus::InProgress);
                },
                'library as dropped_count' => function ($query) {
                    $query->where('status', '=', UserLibraryStatus::Dropped);
                },
                'library as planning_count' => function ($query) {
                    $query->where('status', '=', UserLibraryStatus::Planning);
                },
                'library as completed_count' => function ($query) {
                    $query->where('status', '=', UserLibraryStatus::Completed);
                },
                'library as on_hold_count' => function ($query) {
                    $query->where('status', '=', UserLibraryStatus::OnHold);
                },
                'library as interested_count' => function ($query) {
                    $query->where('status', '=', UserLibraryStatus::Interested);
                },
                'library as ignored_count' => function ($query) {
                    $query->where('status', '=', UserLibraryStatus::Ignored);
                },
            ])
            ->chunkById($chunkSize, function (Collection $models) use ($bar) {
                DB::transaction(function () use ($models, $bar) {
                    $models->each(function ($model) use ($bar) {
                        // Find or create media stat for the model
                        $mediaStat = $model->mediaStat;

                        if (empty($mediaStat)) {
                            $mediaStat = MediaStat::create([
                                'model_type' => $model->getMorphClass(),
                                'model_id' => $model->id,
                            ]);
                        }

                        // Get library count per status
                        $planningCount = $model->planning_count;
                        $inProgressCount = $model->in_progress_count;
                        $completedCount = $model->completed_count;
                        $onHoldCount = $model->on_hold_count;
                        $droppedCount = $model->dropped_count;
                        $interestedCount = $model->interested_count;
                        $ignoredCount = $model->ignored_count;

                        // Sum library count of all statuses
                        $modelCount = $planningCount + $inProgressCount
                            + $completedCount + $onHoldCount
                            + $droppedCount + $interestedCount
                            + $ignoredCount;

                        // Update media stat
                        $mediaStat->updateQuietly([
                            'model_count' => $modelCount,
                            'planning_count' => $planningCount,
                            'in_progress_count' => $inProgressCount,
                            'completed_count' => $completedCount,
                            'on_hold_count' => $onHoldCount,
                            'dropped_count' => $droppedCount,
                            'interested_count' => $interestedCount,
                            'ignored_count' => $ignoredCount,
                        ]);

                        $bar->advance();
                    });
                });
            });

        $bar->finish();

        DB::enableQueryLog();

        return Command::SUCCESS;
    }
}
