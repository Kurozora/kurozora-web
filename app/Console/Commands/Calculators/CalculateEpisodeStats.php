<?php

namespace App\Console\Commands\Calculators;

use App\Models\Episode;
use App\Models\MediaStat;
use App\Models\UserWatchedEpisode;
use DB;
use Illuminate\Console\Command;

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
        // Get episode_id, status and count per status
        $userWatchedEpisodes = UserWatchedEpisode::select(['episode_id', DB::raw('COUNT(*) as total')])
            ->groupBy('episode_id')
            ->get();

        // Get unique episode id's
        $episodeIDs = $userWatchedEpisodes->unique('episode_id')
            ->pluck('episode_id');

        foreach ($episodeIDs as $episodeID) {
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
            $mediaStat->update([
                'model_count' => $modelCount,
            ]);
        }

        return Command::SUCCESS;
    }
}
