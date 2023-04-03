<?php

namespace App\Console\Commands\Calculators;

use App\Models\Game;
use App\Models\View;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class CalculateGameViews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:game_views';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates the total views of an game.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Game::with('views')
            ->join(View::TABLE_NAME, View::TABLE_NAME . '.viewable_id', '=', Game::TABLE_NAME . '.id')
            ->where(View::TABLE_NAME . '.viewable_type', Game::class)
            ->select(Game::TABLE_NAME . '.*')
            ->groupBy('id')
            ->chunk(1000, function (Collection $games) {
                /** @var Game $game */
                foreach ($games as $game) {
                    $totalViewCount = $game->views_count + $game->views()->count();

                    $game->update([
                        'view_count' => $totalViewCount
                    ]);
                }
            });

        // Delete the calculated views
        View::where('viewable_type', '=', Game::class)
            ->forceDelete();

        return Command::SUCCESS;
    }
}
