<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserLibraryKind;
use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Traits\Controller\ResolvesTitle;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TitleCastController extends Controller
{
    use ResolvesTitle;

    /**
     * Show the cast of a title.
     *
     * @param Request    $request
     * @param Anime|null $anime
     * @param Manga|null $manga
     * @param Game|null  $game
     *
     * @return Application|Factory|View
     */
    public function index(Request $request, ?Anime $anime = null, ?Manga $manga = null, ?Game $game = null): Application|Factory|View
    {
        $kind = $this->kind($request);
        $title = $this->title($request, $anime, $manga, $game)
            ->loadMissing(['media', 'translation']);

        $eagerLoads = match ($kind) {
            UserLibraryKind::Manga => [
                'character' => function ($query) {
                    $query->with(['media', 'translation']);
                },
                'castRole',
            ],
            default => [
                'person' => function ($query) {
                    $query->with(['media']);
                },
                'character' => function ($query) {
                    $query->with(['media', 'translation']);
                },
                'castRole',
            ],
        };

        return view('title.cast', $this->titleViewData($kind, $title) + [
            'cast' => $title->cast()
                ->with($eagerLoads)
                ->paginate(25),
            'canonicalUrl' => match ($kind) {
                UserLibraryKind::Anime => route('anime.cast', $title),
                UserLibraryKind::Manga => route('manga.cast', $title),
                UserLibraryKind::Game => route('games.cast', $title),
            },
        ]);
    }
}
