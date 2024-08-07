<?php

namespace App\Console\Commands\Calculators;

use App\Models\Episode;
use App\Models\MediaStat;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class CalculateEpisodeStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:episode_stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate stats for episodes with sufficient data.';

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
        $model = Episode::withoutGlobalScopes();

        $this->info('Calculating stats for: ' . Episode::class);

        $totalCount = $model->whereHas('user_watched_episodes')
            ->count();
        $bar = $this->output->createProgressBar($totalCount);

        $model->whereHas('user_watched_episodes')
            ->with([
                'mediaStat'
            ])
            ->withCount([
                'user_watched_episodes as watch_count',
            ])
            ->chunkById($chunkSize, function (Collection $models) use ($bar) {
                DB::transaction(function () use ($models, $bar) {
                    $models->each(function ($model) use ($bar) {
                        // Find or create media stat for the episode
                        $mediaStat = $model->mediaStat;

                        if (empty($mediaStat)) {
                            $mediaStat = MediaStat::create([
                                'model_type' => $model->getMorphClass(),
                                'model_id' => $model->id,
                            ]);
                        }

                        // Get all current episode records from user watched episode
                        $watchCount = $model->watch_count;

                        // Update media stat
                        $mediaStat->updateQuietly([
                            'model_count' => $watchCount,
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
