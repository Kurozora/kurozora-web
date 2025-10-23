<?php

namespace App\Console\Commands\Calculators;

use App\Models\MediaStat;
use DB;
use Illuminate\Console\Command;

class CalculateRankings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:rankings
                            {model? : Class name of model to calculate rank}
                            {--g|global : Whether to calculate global rank}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates the ranks of the specified model type.';

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
        $page = 1;
        $perPage = 500;
        $class = $this->argument('model');
        $isGlobal = $this->option('global');

        if (!empty($class) && $isGlobal) {
            $this->error('Specifying `model` and `global` at the same time isn’t supported.');
            return Command::FAILURE;
        }

        if ($class === 'all') {
            MediaStat::withoutGlobalScopes()
                ->distinct()
                ->select(['model_type'])
                ->pluck('model_type')
                ->each(function ($modelType) {
                    $this->call('calculate:rankings', ['model' => $modelType]);
                });

            return Command::SUCCESS;
        }

        $this->info('Calculating rankings for: ' . $class);

        $mediaStat = MediaStat::withoutGlobalScopes()
            ->orderBy('in_progress_count', 'desc')
            ->orderBy('rating_average', 'desc')
            ->orderBy('rating_count', 'desc');

        if (!empty($class)) {
            $mediaStat->where('model_type', '=', $class);
        }

        $mediaStat->chunk($perPage, function ($mediaStats) use ($class, $isGlobal, $perPage, &$page) {
            DB::transaction(function () use ($mediaStats, $class, $isGlobal, $perPage, &$page) {
                foreach ($mediaStats as $index => $mediaStat) {
                    $rank = ($page - 1) * $perPage + $index + 1;

                    if ($isGlobal) {
                        $mediaStat->rank_global = $rank;
                    } else {
                        $mediaStat->rank_total = $rank;
                        $mediaStat->model_type::withoutGlobalScopes()
                            ->where('id', '=', $mediaStat->model_id)
                            ->update([
                                'rank_total' => $rank
                            ]);
                    }
                    $mediaStat->save();
                }

                $key = $mediaStats->last()->id;
                $this->line('<comment>Calculated [' . $class . '] models up to ID:</comment> ' . $key);

                $page++;
            });
        });

        return Command::SUCCESS;
    }
}
