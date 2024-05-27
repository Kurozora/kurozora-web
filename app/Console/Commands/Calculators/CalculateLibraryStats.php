<?php

namespace App\Console\Commands\Calculators;

use App\Enums\UserLibraryStatus;
use App\Models\MediaStat;
use App\Models\UserLibrary;
use DB;
use Illuminate\Console\Command;

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

        $class = $this->argument('model');

        $this->info('Calculating stats for: ' . $class);

        // Get trackable_id, status and count per status
        $userLibraries = UserLibrary::select(['trackable_id', 'trackable_type', 'status', DB::raw('COUNT(*) as total')])
            ->where('trackable_type', '=', $class)
            ->groupBy(['trackable_id', 'trackable_type', 'status'])
            ->get();

        $bar = $this->output->createProgressBar($userLibraries->count());

        // Get unique model id's
        DB::transaction(function () use ($class, $userLibraries, $bar) {
            $userLibraries->unique('trackable_id')
                ->pluck('trackable_id')
                ->each(function ($modelID) use ($bar, $class, $userLibraries) {
                    // Find or create media stat for the model
                    $mediaStat = MediaStat::firstOrCreate([
                        'model_type' => $class,
                        'model_id' => $modelID,
                    ]);

                    // Get all current model records from user library
                    $modelInLibrary = $userLibraries->where('trackable_id', '=', $modelID);

                    // Get library status' for the model
                    $planningCount = $modelInLibrary->where('status', '=', UserLibraryStatus::Planning);
                    $inProgressCount = $modelInLibrary->where('status', '=', UserLibraryStatus::InProgress);
                    $completedCount = $modelInLibrary->where('status', '=', UserLibraryStatus::Completed);
                    $onHoldCount = $modelInLibrary->where('status', '=', UserLibraryStatus::OnHold);
                    $droppedCount = $modelInLibrary->where('status', '=', UserLibraryStatus::Dropped);
                    $ignoredCount = $modelInLibrary->where('status', '=', UserLibraryStatus::Ignored);

                    // Get all counts
                    $planningCount = $planningCount->values()[0]['total'] ?? 0;
                    $inProgressCount = $inProgressCount->values()[0]['total'] ?? 0;
                    $completedCount = $completedCount->values()[0]['total'] ?? 0;
                    $onHoldCount = $onHoldCount->values()[0]['total'] ?? 0;
                    $droppedCount = $droppedCount->values()[0]['total'] ?? 0;
                    $ignoredCount = $ignoredCount->values()[0]['total'] ?? 0;
                    $modelCount = $planningCount + $inProgressCount + $completedCount + $onHoldCount + $droppedCount + $ignoredCount;

                    // Update media stat
                    $mediaStat->updateQuietly([
                        'model_count' => $modelCount,
                        'planning_count' => $planningCount,
                        'in_progress_count' => $inProgressCount,
                        'completed_count' => $completedCount,
                        'on_hold_count' => $onHoldCount,
                        'dropped_count' => $droppedCount,
                        'ignored_count' => $ignoredCount,
                    ]);

                    $bar->advance();
                });

            $bar->finish();
        });

        DB::enableQueryLog();

        return Command::SUCCESS;
    }
}
