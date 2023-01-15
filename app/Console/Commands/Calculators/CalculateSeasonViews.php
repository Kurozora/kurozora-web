<?php

namespace App\Console\Commands\Calculators;

use App\Models\Season;
use App\Models\View;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class CalculateSeasonViews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:season_views';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates the total views of an season.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Season::with('views')
            ->join(View::TABLE_NAME, View::TABLE_NAME . '.viewable_id', '=', Season::TABLE_NAME . '.id')
            ->where(View::TABLE_NAME . '.viewable_type', Season::class)
            ->select(Season::TABLE_NAME . '.*')
            ->groupBy('id')
            ->chunk(1000, function (Collection $seasons) {
                /** @var Season $season */
                foreach ($seasons as $season) {
                    $totalViewCount = $season->views_count + $season->views()->count();

                    $season->update([
                        'view_count' => $totalViewCount
                    ]);
                }
            });

        // Delete the calculated views
        View::where('viewable_type', '=', Season::class)
            ->forceDelete();

        return Command::SUCCESS;
    }
}
