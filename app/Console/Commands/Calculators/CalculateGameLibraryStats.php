<?php

namespace App\Console\Commands\Calculators;

use App\Enums\UserLibraryStatus;
use App\Models\Game;
use App\Models\MediaStat;
use App\Models\UserLibrary;
use DB;
use Illuminate\Console\Command;

class CalculateGameLibraryStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:game_library_stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate library stats for game with sufficient data.';

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
        // Get trackable_id, status and count per status
        $userLibraries = UserLibrary::select(['trackable_id', 'trackable_type', 'status', DB::raw('COUNT(*) as total')])
            ->where('trackable_type', '=', Game::class)
            ->groupBy(['trackable_id', 'trackable_type', 'status'])
            ->get();

        // Get unique game id's
        $gameIDs = $userLibraries->unique('trackable_id')
            ->pluck('trackable_id');

        foreach ($gameIDs as $gameID) {
            // Find or create media stat for the game
            $mediaStat = MediaStat::firstOrCreate([
                'model_type' => Game::class,
                'model_id' => $gameID,
            ]);

            // Get all current game records from user library
            $gameInLibrary = $userLibraries->where('trackable_id', '=', $gameID);

            // Get library status' for the game
            $planningCount = $gameInLibrary->where('status', '=', UserLibraryStatus::Planning);
            $inProgressCount = $gameInLibrary->where('status', '=', UserLibraryStatus::InProgress);
            $completedCount = $gameInLibrary->where('status', '=', UserLibraryStatus::Completed);
            $onHoldCount = $gameInLibrary->where('status', '=', UserLibraryStatus::OnHold);
            $droppedCount = $gameInLibrary->where('status', '=', UserLibraryStatus::Dropped);

            // Get all counts
            $planningCount = $planningCount->values()[0]['total'] ?? 0;
            $inProgressCount = $inProgressCount->values()[0]['total'] ?? 0;
            $completedCount = $completedCount->values()[0]['total'] ?? 0;
            $onHoldCount = $onHoldCount->values()[0]['total'] ?? 0;
            $droppedCount = $droppedCount->values()[0]['total'] ?? 0;
            $modelCount = $planningCount + $inProgressCount + $completedCount + $onHoldCount + $droppedCount;

            // Update media stat
            $mediaStat->update([
                'model_count'       => $modelCount,
                'planning_count'    => $planningCount,
                'in_progress_count' => $inProgressCount,
                'completed_count'   => $completedCount,
                'on_hold_count'     => $onHoldCount,
                'dropped_count'     => $droppedCount,
            ]);
        }

        return Command::SUCCESS;
    }
}
