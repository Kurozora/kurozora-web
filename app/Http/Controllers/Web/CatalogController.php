<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserLibraryKind;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetSearchIndexRequest;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MediaType;
use App\Support\SearchCriteria;
use App\Support\SearchIndex;
use App\Traits\Controller\PaginatesTitles;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class CatalogController extends Controller
{
    use PaginatesTitles;

    /**
     * Show the catalog of a kind.
     *
     * @param GetSearchIndexRequest $request
     * @param int                   $kind
     *
     * @return Application|Factory|View
     */
    public function index(GetSearchIndexRequest $request, int $kind): Application|Factory|View
    {
        $modelClass = match ($kind) {
            UserLibraryKind::Anime => Anime::class,
            UserLibraryKind::Manga => Manga::class,
            UserLibraryKind::Game => Game::class,
        };
        $mediaTypeKind = match ($kind) {
            UserLibraryKind::Anime => 'anime',
            UserLibraryKind::Manga => 'manga',
            UserLibraryKind::Game => 'game',
        };
        $user = $request->user();

        $criteria = SearchCriteria::fromRequest(
            $request,
            filters: $modelClass::webSearchFilters(),
            orders: $modelClass::webSearchOrders(),
            searchTypes: MediaType::where('type', '=', $mediaTypeKind)
                ->orderBy('name')
                ->pluck('name', 'id')
                ->prepend(__('All'), 'all')
                ->toArray(),
        );

        $searchIndex = (new SearchIndex($modelClass, $criteria))
            ->hydrate(fn (Builder $query) => $this->hydrateTitles($query));

        if ($user !== null && !empty($criteria->libraryStatuses())) {
            $searchIndex->library($user, $criteria->libraryStatuses());
        }

        $copy = match ($kind) {
            UserLibraryKind::Anime => [
                'heading' => __('Anime'),
                'description' => __('Browse all anime on :x. Join the :x community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => config('app.name')]),
                'canonicalUrl' => route('anime.index'),
                'randomUrl' => route('random.anime'),
                'randomLabel' => 'random anime',
                'adaptedUrl' => null,
                'trailersUrl' => route('anime.trailers'),
                'emptyImage' => 'empty_anime_library.webp',
                'emptyHeading' => __('Anime Not Found'),
                'emptyDescription' => __('No anime found with the selected criteria.'),
            ],
            UserLibraryKind::Manga => [
                'heading' => __('Manga'),
                'description' => __('Browse all manga on :x. Join the :x community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => config('app.name')]),
                'canonicalUrl' => route('manga.index'),
                'randomUrl' => route('random.manga'),
                'randomLabel' => 'random manga',
                'adaptedUrl' => route('manga.adapted'),
                'trailersUrl' => null,
                'emptyImage' => 'empty_manga_library.webp',
                'emptyHeading' => __('Manga Not Found'),
                'emptyDescription' => __('No manga found with the selected criteria.'),
            ],
            UserLibraryKind::Game => [
                'heading' => __('Games'),
                'description' => __('Browse all games on :x. Join the :x community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => config('app.name')]),
                'canonicalUrl' => route('games.index'),
                'randomUrl' => route('random.games'),
                'randomLabel' => 'random game',
                'adaptedUrl' => route('games.adapted'),
                'trailersUrl' => route('games.trailers'),
                'emptyImage' => 'empty_game_library.webp',
                'emptyHeading' => __('Games Not Found'),
                'emptyDescription' => __('No games found with the selected criteria.'),
            ],
        };

        return view('catalog.index', $copy + [
            'kind' => $kind,
            'criteria' => $criteria,
            'results' => $searchIndex->paginate()
                ->withQueryString(),
        ]);
    }
}
