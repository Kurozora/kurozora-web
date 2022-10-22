<?php

namespace App\Console\Commands\Calculators;

use App\Models\Anime;
use App\Models\View;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class CalculateAnimeViews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:anime_views';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates the total views of an anime.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Anime::with('views')
            ->join(View::TABLE_NAME, View::TABLE_NAME . '.viewable_id', '=', Anime::TABLE_NAME . '.id')
            ->where(View::TABLE_NAME . '.viewable_type', Anime::class)
            ->select(Anime::TABLE_NAME . '.*')
            ->groupBy('id')
            ->chunk(1000, function (Collection $animes) {
                /** @var Anime $anime */
                foreach ($animes as $anime) {
                    $totalViewCount = $anime->views_count + $anime->views()->count();

                    $anime->update([
                        'view_count' => $totalViewCount
                    ]);
                }
            });

        // Delete the calculated views
        View::where('viewable_type', '=', Anime::class)
            ->forceDelete();

        return Command::SUCCESS;
    }
}
