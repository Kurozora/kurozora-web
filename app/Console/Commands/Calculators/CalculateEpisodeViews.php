<?php

namespace App\Console\Commands\Calculators;

use App\Models\Episode;
use App\Models\View;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class CalculateEpisodeViews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:episode_views';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates the total views of an episode.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Episode::with('views')
            ->join(View::TABLE_NAME, View::TABLE_NAME . '.viewable_id', '=', Episode::TABLE_NAME . '.id')
            ->where(View::TABLE_NAME . '.viewable_type', Episode::class)
            ->select(Episode::TABLE_NAME . '.*')
            ->groupBy('id')
            ->chunk(1000, function (Collection $episodes) {
                /** @var Episode $episode */
                foreach ($episodes as $episode) {
                    $totalViewCount = $episode->views_count + $episode->views()->count();

                    $episode->update([
                        'view_count' => $totalViewCount
                    ]);
                }
            });

        // Delete the calculated views
        View::where('viewable_type', '=', Episode::class)
            ->forceDelete();

        return Command::SUCCESS;
    }
}
