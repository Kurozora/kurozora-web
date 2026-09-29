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

class TitleStaffController extends Controller
{
    use ResolvesTitle;

    /**
     * Show the staff of a title.
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

        return view('title.staff', $this->titleViewData($kind, $title) + [
            'mediaStaff' => $title->mediaStaff()
                ->with([
                    'person' => function ($query) {
                        $query->with(['media']);
                    },
                    'staffRole',
                ])
                ->paginate(25),
            'canonicalUrl' => match ($kind) {
                UserLibraryKind::Anime => route('anime.staff', $title),
                UserLibraryKind::Manga => route('manga.staff', $title),
                UserLibraryKind::Game => route('games.staff', $title),
            },
        ]);
    }
}
