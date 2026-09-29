<?php

namespace App\Traits\Controller;

use App\Enums\UserLibraryKind;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use Illuminate\Http\Request;

trait ResolvesTitle
{
    /**
     * The library kind of the current route.
     *
     * @param Request $request
     *
     * @return int
     */
    protected function kind(Request $request): int
    {
        return (int) $request->route('kind');
    }

    /**
     * The title bound to the current route.
     *
     * @param Request    $request
     * @param Anime|null $anime
     * @param Manga|null $manga
     * @param Game|null  $game
     *
     * @return Anime|Manga|Game
     */
    protected function title(Request $request, ?Anime $anime, ?Manga $manga, ?Game $game): Anime|Manga|Game
    {
        return match ($this->kind($request)) {
            UserLibraryKind::Anime => $anime,
            UserLibraryKind::Manga => $manga,
            UserLibraryKind::Game => $game,
        };
    }

    /**
     * The view data shared by every sub-page of a title.
     *
     * @param int              $kind
     * @param Anime|Manga|Game $title
     *
     * @return array
     */
    protected function titleViewData(int $kind, Anime|Manga|Game $title): array
    {
        return [
            'kind' => $kind,
            'parent' => $title,
            'ogType' => match ($kind) {
                UserLibraryKind::Anime, UserLibraryKind::Game => 'video.tv_show',
                UserLibraryKind::Manga => 'book',
            },
            'ogImagePoster' => match ($kind) {
                UserLibraryKind::Anime, UserLibraryKind::Manga => 'anime_poster.webp',
                UserLibraryKind::Game => 'game_poster.webp',
            },
            'appArgumentSegment' => match ($kind) {
                UserLibraryKind::Anime => 'anime',
                UserLibraryKind::Manga => 'manga',
                UserLibraryKind::Game => 'games',
            },
        ];
    }
}
