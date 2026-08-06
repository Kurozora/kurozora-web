<?php

namespace App\Console\Commands\Calculators;

use App\Models\View;
use DB;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Telescope\Telescope;
use Pulse;

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
        Pulse::stopRecording();
        Telescope::stopRecording();

        $chunkSize = 2000;
        $class = $this->argument('model');

        if ($class === 'all') {
            View::withoutGlobalScopes()
                ->distinct()
                ->select(['viewable_type'])
                ->pluck('viewable_type')
                ->each(function ($modelType) {
                    $this->call('calculate:views', ['model' => $modelType]);
                    $this->newLine();
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
                    $idValues = [];

                    foreach ($views as $view) {
                        $model = $view->viewable;

                        if (empty($model)) {
                            $bar->advance();

                            continue;
                        }

                        $idValues[(int) $model->id] = (int) $model->view_count + (int) $view->views_count;
                        $bar->advance();
                    }

                    if (!empty($idValues)) {
                        $ids = array_keys($idValues);
                        $cases = '';
                        foreach ($idValues as $id => $count) {
                            $cases .= sprintf(' WHEN %d THEN %d', $id, $count);
                        }

                        $class::withoutGlobalScopes()
                            ->whereIn('id', $ids)
                            ->update(['view_count' => DB::raw('CASE id' . $cases . ' END')]);
                    }

                    // Delete the calculated views
                    $viewableIDs = $views->pluck('viewable_id')
                        ->toArray();

                    View::where('viewable_type', '=', $class)
                        ->whereIn('viewable_id', $viewableIDs)
                        ->forceDelete();
                });
            }, 'viewable_id');

        $bar->finish();

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }
}
