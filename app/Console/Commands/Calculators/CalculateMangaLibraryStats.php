<?php

namespace App\Console\Commands\Calculators;

use App\Enums\UserLibraryStatus;
use App\Models\Manga;
use App\Models\MediaStat;
use App\Models\UserLibrary;
use DB;
use Illuminate\Console\Command;

class CalculateMangaLibraryStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:manga_library_stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate library stats for manga with sufficient data.';

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
            ->where('trackable_type', '=', Manga::class)
            ->groupBy(['trackable_id', 'trackable_type', 'status'])
            ->get();

        // Get unique manga id's
        $mangaIDs = $userLibraries->unique('trackable_id')
            ->pluck('trackable_id');

        foreach ($mangaIDs as $mangaID) {
            // Find or create media stat for the manga
            $mediaStat = MediaStat::firstOrCreate([
                'model_type' => Manga::class,
                'model_id' => $mangaID,
            ]);

            // Get all current manga records from user library
            $mangaInLibrary = $userLibraries->where('trackable_id', '=', $mangaID);

            // Get library status' for the manga
            $planningCount = $mangaInLibrary->where('status', '=', UserLibraryStatus::Planning);
            $inProgressCount = $mangaInLibrary->where('status', '=', UserLibraryStatus::InProgress);
            $completedCount = $mangaInLibrary->where('status', '=', UserLibraryStatus::Completed);
            $onHoldCount = $mangaInLibrary->where('status', '=', UserLibraryStatus::OnHold);
            $droppedCount = $mangaInLibrary->where('status', '=', UserLibraryStatus::Dropped);

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
                'watching_count'    => $inProgressCount,
                'completed_count'   => $completedCount,
                'on_hold_count'     => $onHoldCount,
                'dropped_count'     => $droppedCount,
            ]);
        }

        return Command::SUCCESS;
    }
}
