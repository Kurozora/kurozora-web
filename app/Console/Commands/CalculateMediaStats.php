<?php

namespace App\Console\Commands;

use App\Enums\UserLibraryStatus;
use App\Models\Anime;
use App\Models\MediaStat;
use App\Models\UserLibrary;
use DB;
use Illuminate\Console\Command;

class CalculateMediaStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:media_stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate media stats for anime with sufficient data.';

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
        // Get anime_id, status and count per status
        $userLibraries = UserLibrary::select(['anime_id', 'status', DB::raw('COUNT(*)')])
            ->groupBy(['anime_id', 'status'])
            ->get();

        // Get unique anime id's
        $animeIDs = $userLibraries->unique('anime_id')
            ->pluck('anime_id');

        foreach ($animeIDs as $animeID) {
            // Find or create media stat for the anime
            $mediaStat = MediaStat::firstOrCreate([
                'model_type' => Anime::class,
                'model_id' => $animeID,
            ]);

            // Get all current anime records from user library
            $animeInLibrary = $userLibraries->where('anime_id', '=', $animeID);

            // Get library status' for the anime
            $planningCount = $animeInLibrary->where('status', '=', UserLibraryStatus::Planning);
            $watchingCount = $animeInLibrary->where('status', '=', UserLibraryStatus::Watching);
            $completedCount = $animeInLibrary->where('status', '=', UserLibraryStatus::Completed);
            $onHoldCount = $animeInLibrary->where('status', '=', UserLibraryStatus::OnHold);
            $droppedCount = $animeInLibrary->where('status', '=', UserLibraryStatus::Dropped);

            // Update media stat
            $mediaStat->update([
                'planning_count'    => $planningCount->values()[0]['COUNT(*)'] ?? 0,
                'watching_count'    => $watchingCount->values()[0]['COUNT(*)'] ?? 0,
                'completed_count'   => $completedCount->values()[0]['COUNT(*)'] ?? 0,
                'on_hold_count'     => $onHoldCount->values()[0]['COUNT(*)'] ?? 0,
                'dropped_count'     => $droppedCount->values()[0]['COUNT(*)'] ?? 0,
            ]);
        }

        return Command::SUCCESS;
    }
}