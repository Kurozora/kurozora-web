<?php

namespace App\Console\Commands\Calculators;

use App\Models\View;
use Artisan;
use DB;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class CalculateViews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:views
                            {model : Class name of model to calculate rank}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates the total views of the specified model type.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        DB::disableQueryLog();

        $chunkSize = 1000;
        $class = $this->argument('model');

        if ($class === 'all') {
            View::withoutGlobalScopes()
                ->distinct()
                ->select(['viewable_type'])
                ->pluck('viewable_type')
                ->each(function ($modelType) {
                    Artisan::call('calculate:views', ['model' => $modelType]);
                });

            return Command::SUCCESS;
        }

        $this->info('Calculating views for: ' . $class);

        $totalCount = View::select(['viewable_type', 'viewable_id'])
            ->where('viewable_type', '=', $class)
            ->distinct(['viewable_type', 'viewable_id']) // Method takes in a parameter using `func_get_args()`
            ->count();
        $bar = $this->output->createProgressBar($totalCount);

        View::select(['viewable_type', 'viewable_id', DB::raw('COUNT(*) as views_count')])
            ->where('viewable_type', '=', $class)
            ->groupBy('viewable_type', 'viewable_id')
            ->with('viewable')
            ->chunkById($chunkSize, function (Collection $views) use ($class, $bar) {
                DB::transaction(function () use ($class, $bar, $views) {
                    $views->each(function (View $view) use ($bar) {
                        $model = $view->viewable;

                        if (!empty($model)) {
                            // Update the view_count property
                            $model->update([
                                'view_count' => $model->view_count + $view->views_count,
                            ]);
                        }

                        $bar->advance();
                    });

                    // Delete the calculated views
                    $viewableIDs = $views->pluck('viewable_id')
                        ->toArray();

                    View::where('viewable_type', '=', $class)
                        ->whereIn('viewable_id', $viewableIDs)
                        ->forceDelete();
                });
            }, 'viewable_id');

        $bar->finish();

        DB::enableQueryLog();

        return Command::SUCCESS;
    }
}
