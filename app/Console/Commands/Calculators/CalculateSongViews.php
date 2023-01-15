<?php

namespace App\Console\Commands\Calculators;

use App\Models\Song;
use App\Models\View;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class CalculateSongViews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:song_views';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates the total views of an song.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Song::with('views')
            ->join(View::TABLE_NAME, View::TABLE_NAME . '.viewable_id', '=', Song::TABLE_NAME . '.id')
            ->where(View::TABLE_NAME . '.viewable_type', Song::class)
            ->select(Song::TABLE_NAME . '.*')
            ->groupBy('id')
            ->chunk(1000, function (Collection $songs) {
                /** @var Song $song */
                foreach ($songs as $song) {
                    $totalViewCount = $song->views_count + $song->views()->count();

                    $song->update([
                        'view_count' => $totalViewCount
                    ]);
                }
            });

        // Delete the calculated views
        View::where('viewable_type', '=', Song::class)
            ->forceDelete();

        return Command::SUCCESS;
    }
}
