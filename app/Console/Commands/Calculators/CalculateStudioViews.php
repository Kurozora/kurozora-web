<?php

namespace App\Console\Commands\Calculators;

use App\Models\Studio;
use App\Models\View;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class CalculateStudioViews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:studio_views';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates the total views of an studio.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Studio::with('views')
            ->join(View::TABLE_NAME, View::TABLE_NAME . '.viewable_id', '=', Studio::TABLE_NAME . '.id')
            ->where(View::TABLE_NAME . '.viewable_type', Studio::class)
            ->select(Studio::TABLE_NAME . '.*')
            ->groupBy('id')
            ->chunk(1000, function (Collection $studios) {
                /** @var Studio $studio */
                foreach ($studios as $studio) {
                    $totalViewCount = $studio->views_count + $studio->views()->count();

                    $studio->update([
                        'view_count' => $totalViewCount
                    ]);
                }
            });

        // Delete the calculated views
        View::where('viewable_type', '=', Studio::class)
            ->forceDelete();

        return Command::SUCCESS;
    }
}
