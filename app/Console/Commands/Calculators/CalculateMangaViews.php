<?php

namespace App\Console\Commands\Calculators;

use App\Models\Manga;
use App\Models\View;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class CalculateMangaViews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:manga_views';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates the total views of an manga.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Manga::with('views')
            ->join(View::TABLE_NAME, View::TABLE_NAME . '.viewable_id', '=', Manga::TABLE_NAME . '.id')
            ->where(View::TABLE_NAME . '.viewable_type', Manga::class)
            ->select(Manga::TABLE_NAME . '.*')
            ->groupBy('id')
            ->chunk(1000, function (Collection $mangas) {
                /** @var Manga $manga */
                foreach ($mangas as $manga) {
                    $totalViewCount = $manga->views_count + $manga->views()->count();

                    $manga->update([
                        'view_count' => $totalViewCount
                    ]);
                }
            });

        // Delete the calculated views
        View::where('viewable_type', '=', Manga::class)
            ->forceDelete();

        return Command::SUCCESS;
    }
}
