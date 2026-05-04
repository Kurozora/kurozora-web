<?php

namespace App\Console\Commands\Calculators;

use App\Models\Episode;
use App\Models\MediaStat;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Laravel\Telescope\Telescope;
use Pulse;

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
        Pulse::stopRecording();
        Telescope::stopRecording();

        $chunkSize = 2000;

        $this->info('Calculating stats for: ' . Episode::class);

        $base = Episode::withoutGlobalScopes()
            ->whereHas('user_watched_episodes');

        $totalCount = (clone $base)->count();
        $bar = $this->output->createProgressBar($totalCount);

        $base->withCount([
                'user_watched_episodes as watch_count',
            ])
            ->chunkById($chunkSize, function (Collection $models) use ($bar) {
                DB::transaction(function () use ($models, $bar) {
                    $rows = $models->map(fn ($model) => [
                        'model_type' => $model->getMorphClass(),
                        'model_id' => $model->id,
                        'model_count' => (int) $model->watch_count,
                    ])->all();

                    MediaStat::upsert($rows, ['model_type', 'model_id'], ['model_count']);

                    $bar->advance($models->count());
                });
            });

        $bar->finish();

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }
}
