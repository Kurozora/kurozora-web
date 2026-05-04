<?php

namespace App\Console\Commands\Calculators;

use App\Models\MediaStat;
use DB;
use Illuminate\Console\Command;
use Laravel\Telescope\Telescope;
use Pulse;

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
        Pulse::stopRecording();
        Telescope::stopRecording();

        $page = 1;
        $perPage = 2000;
        $byesianPrior = 500; // Bayesian smoothing weight applied against the rating average.
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

            Pulse::startRecording();
            Telescope::startRecording();

            return Command::SUCCESS;
        }

        $this->info('Calculating rankings for: ' . $class);

        $globalAverage = MediaStat::withoutGlobalScopes()
            ->where('rating_count', '>', 0)
            ->when(!empty($class), fn ($query) => $query->where('model_type', '=', $class))
            ->avg('rating_average') ?? 0;

        $bayesianScoreSql = sprintf(
            '((rating_count / (rating_count + %1$d)) * rating_average
              + (%1$d / (rating_count + %1$d)) * ?)
              * LOG10(GREATEST(rating_count, 10)) DESC',
            $byesianPrior
        );

        $rankColumn = $isGlobal ? 'rank_global' : 'rank_total';

        $mediaStat = MediaStat::withoutGlobalScopes()
            ->orderByRaw($bayesianScoreSql, [$globalAverage])
            ->orderBy('rating_count', 'desc');

        if (!empty($class)) {
            $mediaStat->where('model_type', '=', $class);
        }

        $mediaStat->chunk($perPage, function ($mediaStats) use ($class, $isGlobal, $rankColumn, $perPage, &$page) {
            DB::transaction(function () use ($mediaStats, $class, $isGlobal, $rankColumn, $perPage, &$page) {
                $statRows = [];
                $modelGroups = [];

                foreach ($mediaStats as $index => $mediaStat) {
                    $rank = ($page - 1) * $perPage + $index + 1;

                    $statRows[] = [
                        'id' => $mediaStat->id,
                        'model_id' => $mediaStat->model_id,
                        'model_type' => $mediaStat->model_type,
                        $rankColumn => $rank,
                    ];

                    if (!$isGlobal) {
                        $modelGroups[$mediaStat->model_type][(int) $mediaStat->model_id] = $rank;
                    }
                }

                MediaStat::upsert($statRows, ['id'], [$rankColumn]);

                foreach ($modelGroups as $modelType => $idRanks) {
                    $cases = '';
                    foreach ($idRanks as $modelId => $rank) {
                        $cases .= sprintf(' WHEN %d THEN %d', $modelId, $rank);
                    }

                    $modelType::withoutGlobalScopes()
                        ->whereIn('id', array_keys($idRanks))
                        ->update(['rank_total' => DB::raw('CASE id' . $cases . ' END')]);
                }

                $key = $mediaStats->last()->id;
                $this->line('<comment>Calculated [' . $class . '] models up to ID:</comment> ' . $key);

                $page++;
            });
        });

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }
}
