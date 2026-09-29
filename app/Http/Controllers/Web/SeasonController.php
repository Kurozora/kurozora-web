<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Models\Season;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class SeasonController extends Controller
{
    /**
     * Show an anime's seasons.
     *
     * @param Anime $anime
     *
     * @return Application|Factory|View
     */
    public function index(Anime $anime): Application|Factory|View
    {
        $anime->load(['media', 'translation']);

        $seasons = $anime->seasons()
            ->withoutGlobalScopes()
            ->with([
                'media',
                'translation',
            ])
            ->withCount([
                'episodes' => function ($query) {
                    $query->withoutGlobalScopes();
                },
            ])
            ->withAvg([
                'episodesMediaStats as rating_average' => function ($query) {
                    $query->withoutGlobalScopes()
                        ->where('rating_average', '!=', 0);
                },
            ], 'rating_average')
            ->paginate(25);

        $seasons->each(function (Season $season) use ($anime) {
            $season->setRelation('anime', $anime);
        });

        return view('season.details', [
            'anime' => $anime,
            'seasons' => $seasons,
        ]);
    }
}
