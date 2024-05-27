<?php

namespace App\Console\Commands\Calculators;

use App\Models\Episode;
use App\Models\MediaStat;
use App\Models\UserWatchedEpisode;
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

        $this->info('Calculating stats for: ' . Episode::class);

        $totalCount = UserWatchedEpisode::select(['episode_id'])
            ->distinct(['episode_id']) // Method takes in a parameter using `func_get_args()`
            ->count();
        $bar = $this->output->createProgressBar($totalCount);

        // Get episode_id, status and count per status
        UserWatchedEpisode::select(['episode_id', DB::raw('COUNT(*) as total')])
            ->groupBy('episode_id')
            ->chunkById($chunkSize, function (Collection $userWatchedEpisodes) use ($bar) {
                DB::transaction(function () use ($userWatchedEpisodes, $bar) {
                    // Get unique episode id's
                    $userWatchedEpisodes->unique('episode_id')
                        ->pluck('episode_id')
                        ->each(function ($episodeID) use ($bar, $userWatchedEpisodes) {
                            // Find or create media stat for the episode
                            $mediaStat = MediaStat::firstOrCreate([
                                'model_type' => Episode::class,
                                'model_id' => $episodeID,
                            ]);

                            // Get all current episode records from user watched episode
                            $episodeInLibrary = $userWatchedEpisodes->where('episode_id', '=', $episodeID);

                            // Get all counts
                            $modelCount = $episodeInLibrary->values()[0]['total'] ?? 0;

                            // Update media stat
                            $mediaStat->updateQuietly([
                                'model_count' => $modelCount,
                            ]);

                            $bar->advance();
                        });
                });
            }, 'episode_id');

        $bar->finish();

        DB::enableQueryLog();

        return Command::SUCCESS;
    }
}
