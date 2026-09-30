<?php

namespace App\Http\Controllers\Web;

use App\Enums\AdaptedAnimeFilter;
use App\Enums\TrailerSort;
use App\Enums\UserLibraryKind;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetSearchIndexRequest;
use App\Models\Video;
use App\Support\SearchCriteria;
use App\Support\SearchIndex;
use App\Traits\Controller\PaginatesTitles;
use App\Traits\Controller\ResolvesKind;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Scout\Builder as ScoutBuilder;

class CatalogController extends Controller
{
    use PaginatesTitles;
    use ResolvesKind;

    /**
     * The number of days a video counts as a recent release.
     */
    protected const int TRENDING_DAYS = 30;

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
        $modelClass = $this->modelClass($kind);
        $user = $request->user();

        $criteria = SearchCriteria::fromRequest(
            $request,
            filters: $modelClass::webSearchFilters(),
            orders: $modelClass::webSearchOrders(),
            searchTypes: $this->mediaTypes($kind),
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

    /**
     * Show the titles of a kind adapted to anime.
     *
     * @param GetSearchIndexRequest $request
     * @param int                   $kind
     *
     * @return Application|Factory|View
     */
    public function adapted(GetSearchIndexRequest $request, int $kind): Application|Factory|View
    {
        $modelClass = $this->modelClass($kind);
        $user = $request->user();
        $adaptation = $this->adaptationFilter($request);
        $adaptedIds = $modelClass::adaptedToAnime($adaptation)
            ->pluck($modelClass::TABLE_NAME . '.id')
            ->all();

        $criteria = SearchCriteria::fromRequest(
            $request,
            filters: $modelClass::webSearchFilters(),
            orders: $modelClass::webSearchOrders(),
            searchTypes: $this->mediaTypes($kind),
        );

        $searchIndex = (new SearchIndex($modelClass, $criteria))
            ->hydrate(fn (Builder $query) => $this->hydrateTitles($query->whereKey($adaptedIds)))
            ->search(fn (ScoutBuilder $query) => $query->whereIn('id', $adaptedIds));

        if ($user !== null && !empty($criteria->libraryStatuses())) {
            $searchIndex->library($user, $criteria->libraryStatuses());
        }

        $defaultAdaptation = strtolower(AdaptedAnimeFilter::getKey(AdaptedAnimeFilter::Airing));
        $selectedAdaptation = strtolower(AdaptedAnimeFilter::getKey($adaptation->value));

        $copy = match ($kind) {
            UserLibraryKind::Game => [
                'heading' => __('Games Adapted to Anime'),
                'description' => __('Discover games with anime adaptations airing now or slated to premiere on :x.', ['x' => config('app.name')]),
                'canonicalUrl' => route('games.adapted'),
                'randomUrl' => route('games.adapted.random'),
                'randomLabel' => 'random game',
                'parentUrl' => route('games.index'),
                'parentLabel' => __('Games'),
                'emptyImage' => 'empty_game_library.webp',
                'emptyHeading' => __('Games Not Found'),
                'emptyDescription' => __('No games found with the selected criteria.'),
                'dimStorageKey' => 'adapted-dim-library',
            ],
            default => [
                'heading' => __('Manga Adapted to Anime'),
                'description' => __('Discover manga with anime adaptations airing now or slated to premiere on :x.', ['x' => config('app.name')]),
                'canonicalUrl' => route('manga.adapted'),
                'randomUrl' => route('manga.adapted.random'),
                'randomLabel' => 'random manga',
                'parentUrl' => route('manga.index'),
                'parentLabel' => __('Manga'),
                'emptyImage' => 'empty_manga_library.webp',
                'emptyHeading' => __('Manga Not Found'),
                'emptyDescription' => __('No manga found with the selected criteria.'),
                'dimStorageKey' => 'adapted-dim-library',
            ],
        };

        return view('catalog.adapted', $copy + [
            'kind' => $kind,
            'criteria' => $criteria,
            'results' => $searchIndex->paginate()
                ->withQueryString(),
            'adaptation' => $selectedAdaptation,
            'defaultAdaptation' => $defaultAdaptation,
        ]);
    }

    /**
     * Send the visitor to a random title of a kind adapted to anime.
     *
     * @param Request $request
     * @param int     $kind
     *
     * @return RedirectResponse
     */
    public function randomAdapted(Request $request, int $kind): RedirectResponse
    {
        $modelClass = $this->modelClass($kind);
        $item = $modelClass::adaptedToAnime($this->adaptationFilter($request))
            ->inRandomOrder()
            ->first();

        if ($item === null) {
            return back();
        }

        return match ($kind) {
            UserLibraryKind::Anime => to_route('anime.details', $item),
            UserLibraryKind::Manga => to_route('manga.details', $item),
            UserLibraryKind::Game => to_route('games.details', $item),
        };
    }

    /**
     * Show the trailers of a kind.
     *
     * @param GetSearchIndexRequest $request
     * @param int                   $kind
     *
     * @return Application|Factory|View
     */
    public function trailers(GetSearchIndexRequest $request, int $kind): Application|Factory|View
    {
        $modelClass = $this->modelClass($kind);
        $sort = $this->trailerSort($request);

        $criteria = SearchCriteria::fromRequest(
            $request,
            filters: $modelClass::webSearchFilters(),
            orders: $modelClass::webSearchOrders(),
            searchTypes: $this->mediaTypes($kind),
        );

        $parentIds = $criteria->isSearching()
            ? (new SearchIndex($modelClass, $criteria))->keys()
            : null;

        $videos = fn () => $this->applyTrailerSort($this->videoQuery($kind, $sort, $parentIds), $sort);
        $requestedVideoId = $request->integer('video');
        $featuredVideo = $requestedVideoId > 0
            ? $this->videoQuery($kind, $sort, $parentIds)->whereKey($requestedVideoId)->first()
            : null;
        $featuredVideoId = $featuredVideo?->id;
        $featuredVideo ??= $videos()->first();

        $defaultSort = Str::kebab(TrailerSort::getKey(TrailerSort::JustAdded));
        $selectedSort = Str::kebab(TrailerSort::getKey($sort));

        $copy = match ($kind) {
            UserLibraryKind::Game => [
                'heading' => __('Game Trailers'),
                'description' => __('Watch game trailers, teasers and promotional videos on :x. Discover what is coming next and add it to your library.', ['x' => config('app.name')]),
                'canonicalUrl' => route('games.trailers'),
                'randomUrl' => route('games.trailers.random'),
                'randomLabel' => 'random game',
                'appArgument' => 'games/trailers',
                'parentUrl' => route('games.index'),
                'parentLabel' => __('Games'),
                'emptyImage' => 'empty_game_library.webp',
                'emptyHeading' => __('Trailers Not Found'),
                'emptyDescription' => __('No game trailers found with the selected criteria.'),
            ],
            default => [
                'heading' => __('Anime Trailers'),
                'description' => __('Watch anime trailers, teasers and promotional videos on :x. Discover what is coming next and add it to your library.', ['x' => config('app.name')]),
                'canonicalUrl' => route('anime.trailers'),
                'randomUrl' => route('anime.trailers.random'),
                'randomLabel' => 'random anime',
                'appArgument' => 'anime/trailers',
                'parentUrl' => route('anime.index'),
                'parentLabel' => __('Anime'),
                'emptyImage' => 'empty_anime_library.webp',
                'emptyHeading' => __('Trailers Not Found'),
                'emptyDescription' => __('No anime trailers found with the selected criteria.'),
            ],
        };

        return view('catalog.trailers', $copy + [
            'kind' => $kind,
            'criteria' => $criteria,
            'results' => $videos()->paginate($criteria->perPage)
                ->withQueryString(),
            'featuredVideo' => $featuredVideo,
            'featuredVideoId' => $featuredVideoId,
            'sort' => $selectedSort,
            'defaultSort' => $defaultSort,
            'sortOptions' => [
                Str::kebab(TrailerSort::getKey(TrailerSort::JustAdded)) => __('Just Added'),
                Str::kebab(TrailerSort::getKey(TrailerSort::Trending)) => __('Trending'),
                Str::kebab(TrailerSort::getKey(TrailerSort::MostPopular)) => __('Most Popular'),
                Str::kebab(TrailerSort::getKey(TrailerSort::MostAnticipated)) => __('Most Anticipated'),
            ],
        ]);
    }

    /**
     * Send the visitor to a random title of a kind with a trailer.
     *
     * @param GetSearchIndexRequest $request
     * @param int                   $kind
     *
     * @return RedirectResponse
     */
    public function randomTrailer(GetSearchIndexRequest $request, int $kind): RedirectResponse
    {
        $modelClass = $this->modelClass($kind);
        $sort = $this->trailerSort($request);

        $criteria = SearchCriteria::fromRequest(
            $request,
            filters: $modelClass::webSearchFilters(),
            orders: $modelClass::webSearchOrders(),
            searchTypes: $this->mediaTypes($kind),
        );

        $parentIds = $criteria->isSearching()
            ? (new SearchIndex($modelClass, $criteria))->keys()
            : null;

        $video = $this->videoQuery($kind, $sort, $parentIds)
            ->inRandomOrder()
            ->first();

        if ($video?->videoable === null) {
            return back();
        }

        return match ($kind) {
            UserLibraryKind::Game => to_route('games.details', $video->videoable),
            default => to_route('anime.details', $video->videoable),
        };
    }

    /**
     * The adaptation filter the request names.
     *
     * @param Request $request
     *
     * @return AdaptedAnimeFilter
     */
    protected function adaptationFilter(Request $request): AdaptedAnimeFilter
    {
        $key = ucfirst($request->string('show')->value());

        return AdaptedAnimeFilter::hasKey($key)
            ? AdaptedAnimeFilter::fromKey($key)
            : AdaptedAnimeFilter::Airing();
    }

    /**
     * The trailer sort the request names.
     *
     * @param Request $request
     *
     * @return int
     */
    protected function trailerSort(Request $request): int
    {
        $key = Str::studly($request->string('sort')->value());

        return TrailerSort::hasKey($key)
            ? TrailerSort::getValue($key)
            : TrailerSort::JustAdded;
    }

    /**
     * The query of the videos the kind, sort and matched titles allow.
     *
     * @param int        $kind
     * @param int        $sort
     * @param array|null $parentIds
     *
     * @return Builder
     */
    protected function videoQuery(int $kind, int $sort, ?array $parentIds): Builder
    {
        $modelClass = $this->modelClass($kind);
        $releaseDateColumn = $kind === UserLibraryKind::Game ? 'published_at' : 'started_at';
        $user = auth()->user();

        return Video::whereHasMorph('videoable', [$modelClass], function (Builder $query) use ($modelClass, $sort, $parentIds, $releaseDateColumn) {
            if ($sort === TrailerSort::MostAnticipated) {
                $query->whereDate($modelClass::TABLE_NAME . '.' . $releaseDateColumn, '>', today());
            }

            if ($parentIds !== null) {
                $query->whereIn($modelClass::TABLE_NAME . '.id', $parentIds);
            }
        })
            ->whereIn(Video::TABLE_NAME . '.id', $this->representativeVideoIds($modelClass))
            ->with(['videoable' => function (MorphTo $morphTo) use ($modelClass, $user) {
                $morphTo->morphWith([
                    $modelClass => [
                        'genres',
                        'media',
                        'mediaStat',
                        'translation',
                        'library' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user?->id);
                        },
                    ],
                ]);
            }]);
    }

    /**
     * The ids of the one video that stands in for each title.
     *
     * @param string $modelClass
     *
     * @return Builder
     */
    protected function representativeVideoIds(string $modelClass): Builder
    {
        $ranked = Video::selectRaw('id, ROW_NUMBER() OVER (PARTITION BY videoable_id ORDER BY published_at DESC, id DESC) AS ranking')
            ->where('videoable_type', '=', $modelClass);

        return Video::withoutGlobalScopes()
            ->select('id')
            ->fromSub($ranked, 'ranked')
            ->where('ranking', '=', 1);
    }

    /**
     * Limits and orders the given query the way the sort presents videos.
     *
     * @param Builder $query
     * @param int     $sort
     *
     * @return Builder
     */
    protected function applyTrailerSort(Builder $query, int $sort): Builder
    {
        $tableName = Video::TABLE_NAME;
        $publishedAt = $tableName . '.published_at';

        if ($sort === TrailerSort::Trending) {
            $query->whereBetween($publishedAt, [today()->subDays(self::TRENDING_DAYS), now()]);
        }

        match ($sort) {
            TrailerSort::JustAdded => $query->orderByDesc($publishedAt)
                ->orderByDesc($tableName . '.id'),
            default => $query->orderByDesc($tableName . '.view_count')
                ->orderByDesc($tableName . '.id'),
        };

        return $query;
    }
}
