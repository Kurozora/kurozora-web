<?php

namespace App\Http\Controllers\Web;

use App\Enums\SongType;
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

class TitleSongController extends Controller
{
    use ResolvesTitle;

    /**
     * Show the songs of a title grouped by type.
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
        $sort = SongType::asSelectArray();

        $mediaSongs = $title->mediaSongs()
            ->with([
                'song' => function ($query) {
                    $query->with(['media']);
                },
            ])
            ->get()
            ->sortBy(['position'])
            ->groupBy('type.description')
            ->sortKeysUsing(function ($key1, $key2) use ($sort) {
                $key1 = array_search($key1, $sort);
                $key2 = array_search($key2, $sort);

                return $key1 < $key2 ? -1 : 1;
            });

        return view('title.songs', $this->titleViewData($kind, $title) + [
            'mediaSongs' => $mediaSongs,
            'canonicalUrl' => match ($kind) {
                UserLibraryKind::Anime => route('anime.songs', $title),
                UserLibraryKind::Game => route('games.songs', $title),
            },
            'detailsUrl' => match ($kind) {
                UserLibraryKind::Anime => route('anime.details', $title),
                UserLibraryKind::Game => route('games.details', $title),
            },
        ]);
    }
}
