<?php

namespace App\Console\Commands\Calculators;

use App\Jobs\UpdateParentalGuideStatsJob;
use App\Services\ParentalGuideService;
use Illuminate\Console\Command;

class CalculateParentalGuideStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:parental_guide_stats
        {ids : A single ID or comma-separated list of IDs} 
        {type : The fully qualified model class (e.g. App\Models\Anime)} 
        {--sync : Run immediately instead of queueing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate parental guide stats for one or more media models';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $ids = collect(explode(',', $this->argument('ids')))
            ->map(fn ($id) => (int) trim($id))
            ->filter();

        $type = $this->argument('type');

        if (!class_exists($type)) {
            $this->error("Model class [$type] does not exist.");
            return self::FAILURE;
        }

        foreach ($ids as $id) {
            $this->info("Recalculating parental guide stats for [$type:$id]...");

            $job = new UpdateParentalGuideStatsJob($type, $id);

            if ($this->option('sync')) {
                $job->handle(app(ParentalGuideService::class));
            } else {
                dispatch($job);
            }
        }

        $this->info('Done.');
        return self::SUCCESS;
    }
}
