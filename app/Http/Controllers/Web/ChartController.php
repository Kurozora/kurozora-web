<?php

namespace App\Http\Controllers\Web;

use App\Enums\ChartKind;
use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Models\Character;
use App\Models\Episode;
use App\Models\Game;
use App\Models\Manga;
use App\Models\Person;
use App\Models\Song;
use App\Models\Studio;
use App\Traits\Controller\PaginatesTitles;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class ChartController extends Controller
{
    use PaginatesTitles;

    /**
     * Show the top charts page.
     *
     * @return Application|Factory|View
     */
    public function index(): Application|Factory|View
    {
        return view('chart.index', [
            'chartKinds' => ChartKind::getValues(),
        ]);
    }

    /**
     * Show the top chart of a kind.
     *
     * @param string $chart
     *
     * @return Application|Factory|View
     */
    public function show(string $chart): Application|Factory|View
    {
        $query = match ($chart) {
            ChartKind::Anime => $this->hydrateTitles(Anime::query()),
            ChartKind::Characters => Character::with(['media', 'translation']),
            ChartKind::Episodes => Episode::with([
                'anime' => function ($query) {
                    $query->with(['media', 'translation']);
                },
                'media',
                'season' => function ($query) {
                    $query->with(['translation']);
                },
                'translation',
            ])
                ->when(auth()->user(), function ($query, $user) {
                    $query->withExists([
                        'userWatchedEpisodes as isWatched' => function ($query) use ($user) {
                            $query->where('user_id', $user->id)
                                ->completed();
                        },
                    ]);
                }),
            ChartKind::Games => $this->hydrateTitles(Game::query()),
            ChartKind::Manga => $this->hydrateTitles(Manga::query()),
            ChartKind::People => Person::with(['media']),
            ChartKind::Songs => Song::with(['media']),
            ChartKind::Studios => Studio::with(['media']),
        };

        return view('chart.details', [
            'chartKind' => $chart,
            'chart' => $query->where('rank_total', '!=', 0)
                ->orderBy('rank_total')
                ->paginate(25),
        ]);
    }
}
