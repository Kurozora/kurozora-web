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
use Laravel\Telescope\Telescope;
use Pulse;

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
        Pulse::stopRecording();
        Telescope::stopRecording();

        $chunkSize = 2000;
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
                    $rows = $models->map(function ($model) {
                        $modelCount = $model->planning_count + $model->in_progress_count
                            + $model->completed_count + $model->on_hold_count
                            + $model->dropped_count + $model->interested_count
                            + $model->ignored_count;

                        return [
                            'model_type' => $model->getMorphClass(),
                            'model_id' => $model->id,
                            'model_count' => $modelCount,
                            'planning_count' => $model->planning_count,
                            'in_progress_count' => $model->in_progress_count,
                            'completed_count' => $model->completed_count,
                            'on_hold_count' => $model->on_hold_count,
                            'dropped_count' => $model->dropped_count,
                            'interested_count' => $model->interested_count,
                            'ignored_count' => $model->ignored_count,
                        ];
                    })->all();

                    MediaStat::upsert($rows, ['model_type', 'model_id'], [
                        'model_count', 'planning_count', 'in_progress_count', 'completed_count',
                        'on_hold_count', 'dropped_count', 'interested_count', 'ignored_count',
                    ]);

                    $bar->advance($models->count());
                });
            });

        $bar->finish();

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }
}
