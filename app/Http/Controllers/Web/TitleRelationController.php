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
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class TitleRelationController extends Controller
{
    use ResolvesTitle;

    /**
     * Show the anime related to a title.
     *
     * @param Request    $request
     * @param Anime|null $anime
     * @param Manga|null $manga
     * @param Game|null  $game
     *
     * @return Application|Factory|View
     */
    public function anime(Request $request, ?Anime $anime = null, ?Manga $manga = null, ?Game $game = null): Application|Factory|View
    {
        $kind = $this->kind($request);
        $title = $this->title($request, $anime, $manga, $game)
            ->loadMissing(['media']);

        return view('title.related-anime', $this->titleViewData($kind, $title) + [
            'relations' => $this->paginateRelations($title, $title->animeRelations()),
            'pageTitle' => match ($kind) {
                UserLibraryKind::Anime, UserLibraryKind::Game => __('Relations'),
                UserLibraryKind::Manga => __('Adaptations'),
            },
            'pageDescription' => __('An extensive list of sequel, prequel, side story, spin off, and adaptations of :x only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $title->title, 'y' => config('app.name')]),
            'heading' => match ($kind) {
                UserLibraryKind::Anime, UserLibraryKind::Game => __(':x’s Related Anime', ['x' => $title->title]),
                UserLibraryKind::Manga => __(':x’s Adaptations', ['x' => $title->title]),
            },
            'canonicalUrl' => match ($kind) {
                UserLibraryKind::Anime => route('anime.related-anime', $title),
                UserLibraryKind::Manga => route('manga.related-anime', $title),
                UserLibraryKind::Game => route('games.related-anime', $title),
            },
        ]);
    }

    /**
     * Show the manga related to a title.
     *
     * @param Request    $request
     * @param Anime|null $anime
     * @param Manga|null $manga
     * @param Game|null  $game
     *
     * @return Application|Factory|View
     */
    public function manga(Request $request, ?Anime $anime = null, ?Manga $manga = null, ?Game $game = null): Application|Factory|View
    {
        $kind = $this->kind($request);
        $title = $this->title($request, $anime, $manga, $game)
            ->loadMissing(['media']);

        return view('title.related-manga', $this->titleViewData($kind, $title) + [
            'relations' => $this->paginateRelations($title, $title->mangaRelations()),
            'pageTitle' => match ($kind) {
                UserLibraryKind::Anime, UserLibraryKind::Game => __('Adaptations'),
                UserLibraryKind::Manga => __('Relations'),
            },
            'pageDescription' => match ($kind) {
                UserLibraryKind::Anime => __('An extensive list of manga, manhua, manhwa, and light novel adaptations of :x only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $title->title, 'y' => config('app.name')]),
                UserLibraryKind::Manga => __('An extensive list of sequel, prequel, side story, spin off, and adaptations of :x only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $title->title, 'y' => config('app.name')]),
                UserLibraryKind::Game => __('An extensive list of game, mod, and dlc to :x only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $title->title, 'y' => config('app.name')]),
            },
            'heading' => match ($kind) {
                UserLibraryKind::Anime, UserLibraryKind::Game => __(':x’s Adaptations', ['x' => $title->title]),
                UserLibraryKind::Manga => __(':x’s Related Mangas', ['x' => $title->title]),
            },
            'routeSlug' => match ($kind) {
                UserLibraryKind::Anime, UserLibraryKind::Manga => 'related-mangas',
                UserLibraryKind::Game => 'related-literatures',
            },
            'canonicalUrl' => match ($kind) {
                UserLibraryKind::Anime => route('anime.related-mangas', $title),
                UserLibraryKind::Manga => route('manga.related-mangas', $title),
                UserLibraryKind::Game => route('games.related-literatures', $title),
            },
        ]);
    }

    /**
     * Show the games related to a title.
     *
     * @param Request    $request
     * @param Anime|null $anime
     * @param Manga|null $manga
     * @param Game|null  $game
     *
     * @return Application|Factory|View
     */
    public function games(Request $request, ?Anime $anime = null, ?Manga $manga = null, ?Game $game = null): Application|Factory|View
    {
        $kind = $this->kind($request);
        $title = $this->title($request, $anime, $manga, $game)
            ->loadMissing(['media']);

        return view('title.related-games', $this->titleViewData($kind, $title) + [
            'relations' => $this->paginateRelations($title, $title->gameRelations()),
            'pageTitle' => match ($kind) {
                UserLibraryKind::Anime, UserLibraryKind::Manga => __('Games'),
                UserLibraryKind::Game => __('Relations'),
            },
            'pageDescription' => match ($kind) {
                UserLibraryKind::Anime, UserLibraryKind::Manga => __('An extensive list of games of :x only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $title->title, 'y' => config('app.name')]),
                UserLibraryKind::Game => __('An extensive list of sequel, prequel, mods, and dlc to :x only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $title->title, 'y' => config('app.name')]),
            },
            'heading' => match ($kind) {
                UserLibraryKind::Anime, UserLibraryKind::Manga => __(':x’s Games', ['x' => $title->title]),
                UserLibraryKind::Game => __(':x’s Related Games', ['x' => $title->title]),
            },
            'canonicalUrl' => match ($kind) {
                UserLibraryKind::Anime => route('anime.related-games', $title),
                UserLibraryKind::Manga => route('manga.related-games', $title),
                UserLibraryKind::Game => route('games.related-games', $title),
            },
        ]);
    }

    /**
     * Paginate the relations of a title with their related titles.
     *
     * @param Anime|Manga|Game $title
     * @param Relation         $relations
     *
     * @return LengthAwarePaginator
     */
    protected function paginateRelations(Anime|Manga|Game $title, Relation $relations): LengthAwarePaginator
    {
        return $relations
            ->with([
                'related' => function ($query) use ($title) {
                    $title->viewableViaParent($query)
                        ->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating'])
                        ->when(auth()->user(), function ($query, $user) {
                            $query->with(['library' => function ($query) use ($user) {
                                $query->where('user_id', '=', $user->id);
                            }]);
                        });
                },
                'relation',
            ])
            ->paginate(25);
    }
}
