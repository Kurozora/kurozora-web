<?php

namespace App\Http\Controllers\Web;

use App\Enums\ParentalGuideCategory;
use App\Enums\UserLibraryKind;
use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\ParentalGuideEntry;
use App\Traits\Controller\ResolvesTitle;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ParentalGuideController extends Controller
{
    use ResolvesTitle;

    /**
     * Show the parental guide of a title.
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
            ->loadMissing(['media', 'translation', 'tvRating', 'parentalGuideStat']);
        $categories = collect(ParentalGuideCategory::getInstances());

        return view('title.parental-guide', $this->titleViewData($kind, $title) + [
            'entries' => ParentalGuideEntry::visible()
                ->withReason()
                ->where('model_type', '=', $title->getMorphClass())
                ->where('model_id', '=', $title->getKey())
                ->with(ParentalGuideEntry::lockupEagerLoads($request->user()))
                ->orderByDesc('created_at')
                ->get()
                ->groupBy('category'),
            'canonicalUrl' => match ($kind) {
                UserLibraryKind::Anime => route('anime.parentalguide', $title),
                UserLibraryKind::Manga => route('manga.parentalguide', $title),
                UserLibraryKind::Game => route('games.parentalguide', $title),
            },
            'categoryUrls' => match ($kind) {
                UserLibraryKind::Anime => $categories->mapWithKeys(fn (ParentalGuideCategory $category) => [
                    $category->value => route('anime.parentalguide.category', ['anime' => $title, 'category' => $category->urlSlug()]),
                ])->all(),
                UserLibraryKind::Manga => $categories->mapWithKeys(fn (ParentalGuideCategory $category) => [
                    $category->value => route('manga.parentalguide.category', ['manga' => $title, 'category' => $category->urlSlug()]),
                ])->all(),
                UserLibraryKind::Game => $categories->mapWithKeys(fn (ParentalGuideCategory $category) => [
                    $category->value => route('games.parentalguide.category', ['game' => $title, 'category' => $category->urlSlug()]),
                ])->all(),
            },
        ]);
    }

    /**
     * Show the parental guide entries of a single category.
     *
     * @param Request    $request
     * @param Anime|null $anime
     * @param Manga|null $manga
     * @param Game|null  $game
     * @param string     $category
     *
     * @return Application|Factory|View
     */
    public function category(Request $request, ?Anime $anime = null, ?Manga $manga = null, ?Game $game = null, string $category = ''): Application|Factory|View
    {
        $kind = $this->kind($request);
        $title = $this->title($request, $anime, $manga, $game)
            ->loadMissing(['media', 'translation']);
        $categoryEnum = ParentalGuideCategory::fromSlug($category);

        if ($categoryEnum === null) {
            abort(404);
        }

        return view('title.parental-guide-category', $this->titleViewData($kind, $title) + [
            'category' => $categoryEnum,
            'entries' => ParentalGuideEntry::visible()
                ->withReason()
                ->where('model_type', '=', $title->getMorphClass())
                ->where('model_id', '=', $title->getKey())
                ->where('category', '=', $categoryEnum->value)
                ->with(ParentalGuideEntry::lockupEagerLoads($request->user()))
                ->orderByDesc('created_at')
                ->cursorPaginate(20)
                ->withQueryString(),
            'canonicalUrl' => match ($kind) {
                UserLibraryKind::Anime => route('anime.parentalguide.category', ['anime' => $title, 'category' => $categoryEnum->urlSlug()]),
                UserLibraryKind::Manga => route('manga.parentalguide.category', ['manga' => $title, 'category' => $categoryEnum->urlSlug()]),
                UserLibraryKind::Game => route('games.parentalguide.category', ['game' => $title, 'category' => $categoryEnum->urlSlug()]),
            },
            'parentalGuideUrl' => match ($kind) {
                UserLibraryKind::Anime => route('anime.parentalguide', $title),
                UserLibraryKind::Manga => route('manga.parentalguide', $title),
                UserLibraryKind::Game => route('games.parentalguide', $title),
            },
        ]);
    }
}
