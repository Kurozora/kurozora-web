<?php

namespace App\Http\Controllers\Web;

use App\Enums\SeasonOfYear;
use App\Enums\UserLibraryKind;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetSearchIndexRequest;
use App\Models\MediaType;
use App\Support\SearchCriteria;
use App\Support\SearchIndex;
use App\Traits\Controller\PaginatesTitles;
use App\Traits\Controller\ResolvesKind;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;

class BrowseController extends Controller
{
    use PaginatesTitles;
    use ResolvesKind;

    /**
     * Show the upcoming titles of a kind.
     *
     * @param GetSearchIndexRequest $request
     * @param int                   $kind
     *
     * @return Application|Factory|View
     */
    public function upcoming(GetSearchIndexRequest $request, int $kind): Application|Factory|View
    {
        $modelClass = $this->modelClass($kind);
        $dateColumn = $this->dateColumn($kind);
        $user = $request->user();

        $criteria = SearchCriteria::fromRequest(
            $request,
            filters: $modelClass::webSearchFilters(),
            orders: $modelClass::webSearchOrders(),
            searchTypes: $this->mediaTypes($kind),
        );

        $searchIndex = (new SearchIndex($modelClass, $criteria))
            ->hydrate(fn (Builder $query) => $this->hydrateTitles($query))
            ->index(fn (Builder $query) => $query->where($modelClass::TABLE_NAME . '.' . $dateColumn, '>=', yesterday()))
            ->where($dateColumn, ['>=', yesterday()->timestamp]);

        if ($user !== null && !empty($criteria->libraryStatuses())) {
            $searchIndex->library($user, $criteria->libraryStatuses());
        }

        $copy = match ($kind) {
            UserLibraryKind::Anime => [
                'noun' => __('Anime'),
                'heading' => __('Upcoming Anime'),
                'description' => __('Browse the upcoming anime season. Join the :x community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => config('app.name')]),
                'canonicalUrl' => route('anime.upcoming.index'),
                'randomUrl' => route('anime.upcoming.random'),
                'randomLabel' => 'random upcoming anime',
                'emptyImage' => 'empty_anime_library.webp',
                'emptyHeading' => __('No Upcoming Anime'),
                'emptyDescription' => __('There are currently no upcoming anime.'),
            ],
            UserLibraryKind::Manga => [
                'noun' => __('Manga'),
                'heading' => __('Upcoming Manga'),
                'description' => __('Browse the upcoming manga season. Join the :x community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => config('app.name')]),
                'canonicalUrl' => route('manga.upcoming.index'),
                'randomUrl' => route('manga.upcoming.random'),
                'randomLabel' => 'random upcoming manga',
                'emptyImage' => 'empty_manga_library.webp',
                'emptyHeading' => __('No Upcoming Manga'),
                'emptyDescription' => __('There are currently no upcoming manga.'),
            ],
            UserLibraryKind::Game => [
                'noun' => __('Games'),
                'heading' => __('Upcoming Games'),
                'description' => __('Browse the upcoming game season. Join the :x community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => config('app.name')]),
                'canonicalUrl' => route('games.upcoming.index'),
                'randomUrl' => route('games.upcoming.random'),
                'randomLabel' => 'random upcoming game',
                'emptyImage' => 'empty_game_library.webp',
                'emptyHeading' => __('No Upcoming Games'),
                'emptyDescription' => __('There are currently no upcoming games.'),
            ],
        };

        return view('browse.upcoming', $copy + [
            'kind' => $kind,
            'criteria' => $criteria,
            'results' => $searchIndex->paginate()
                ->withQueryString(),
        ]);
    }

    /**
     * Send the visitor to a random upcoming title of a kind.
     *
     * @param int $kind
     *
     * @return RedirectResponse
     */
    public function randomUpcoming(int $kind): RedirectResponse
    {
        $modelClass = $this->modelClass($kind);
        $item = $modelClass::where($this->dateColumn($kind), '>=', yesterday())
            ->randomFirst();

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
     * Show the titles of a kind continuing this season.
     *
     * @param GetSearchIndexRequest $request
     * @param int                   $kind
     *
     * @return Application|Factory|View
     */
    public function continuing(GetSearchIndexRequest $request, int $kind): Application|Factory|View
    {
        $modelClass = $this->modelClass($kind);
        $dateColumn = $this->dateColumn($kind);
        $seasonStartDate = $this->seasonStartDate();
        $user = $request->user();

        $criteria = SearchCriteria::fromRequest(
            $request,
            filters: $modelClass::webSearchFilters(),
            orders: $modelClass::webSearchOrders(),
            searchTypes: $this->mediaTypes($kind),
        );

        $searchIndex = (new SearchIndex($modelClass, $criteria))
            ->hydrate(fn (Builder $query) => $this->hydrateTitles($query))
            ->index(function (Builder $query) use ($modelClass, $dateColumn, $seasonStartDate) {
                $query->where([
                    [$modelClass::TABLE_NAME . '.status_id', '=', 3],
                    [$modelClass::TABLE_NAME . '.' . $dateColumn, '<=', $seasonStartDate->toDateString()],
                ])
                    ->orderBy($dateColumn, 'desc');
            })
            ->where('status_id', 3)
            ->where($dateColumn, ['<=', $seasonStartDate->timestamp]);

        if ($user !== null && !empty($criteria->libraryStatuses())) {
            $searchIndex->library($user, $criteria->libraryStatuses());
        }

        $copy = match ($kind) {
            UserLibraryKind::Anime => [
                'noun' => __('Anime'),
                'heading' => __('Continuing Anime'),
                'description' => __('Browse the anime continuing this season. Join the :x community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => config('app.name')]),
                'canonicalUrl' => route('anime.continuing.index'),
                'randomUrl' => route('anime.continuing.random'),
                'randomLabel' => 'random continuing anime',
                'emptyImage' => 'empty_anime_library.webp',
                'emptyHeading' => __('No Continuing Anime'),
                'emptyDescription' => __('There are currently no ongoing anime.'),
            ],
            UserLibraryKind::Manga => [
                'noun' => __('Manga'),
                'heading' => __('Continuing Manga'),
                'description' => __('Browse the manga continuing this season. Join the :x community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => config('app.name')]),
                'canonicalUrl' => route('manga.continuing.index'),
                'randomUrl' => route('manga.continuing.random'),
                'randomLabel' => 'random continuing manga',
                'emptyImage' => 'empty_manga_library.webp',
                'emptyHeading' => __('No Continuing Manga'),
                'emptyDescription' => __('There are currently no ongoing manga.'),
            ],
        };

        return view('browse.continuing', $copy + [
            'kind' => $kind,
            'criteria' => $criteria,
            'results' => $searchIndex->paginate()
                ->withQueryString(),
        ]);
    }

    /**
     * Send the visitor to a random title of a kind continuing this season.
     *
     * @param int $kind
     *
     * @return RedirectResponse
     */
    public function randomContinuing(int $kind): RedirectResponse
    {
        $modelClass = $this->modelClass($kind);
        $item = $modelClass::where([
            ['status_id', '=', 3],
            [$this->dateColumn($kind), '<=', $this->seasonStartDate()->toDateString()],
        ])->randomFirst();

        if ($item === null) {
            return back();
        }

        return match ($kind) {
            UserLibraryKind::Anime => to_route('anime.details', $item),
            UserLibraryKind::Manga => to_route('manga.details', $item),
        };
    }

    /**
     * Show the titles of a kind released in a season of a year.
     *
     * @param string $year
     * @param string $season
     * @param int    $kind
     *
     * @return Application|Factory|View|RedirectResponse
     */
    public function seasons(string $year, string $season, int $kind): Application|Factory|View|RedirectResponse
    {
        if (!is_numeric($year) || (int) $year < 1917) {
            return $this->redirectToSeasons($kind);
        }

        try {
            $seasonOfYear = SeasonOfYear::fromKey(str($season)->ucfirst());
        } catch (InvalidEnumKeyException) {
            return $this->redirectToSeasons($kind);
        }

        $year = (int) $year;
        $modelClass = $this->modelClass($kind);
        $seasonColumn = match ($kind) {
            UserLibraryKind::Anime => 'air_season',
            UserLibraryKind::Manga, UserLibraryKind::Game => 'publication_season',
        };
        $dateColumn = $this->dateColumn($kind);

        $mediaTypes = MediaType::select(MediaType::TABLE_NAME . '.*')
            ->join($modelClass::TABLE_NAME, function ($join) use ($modelClass, $seasonColumn, $dateColumn, $seasonOfYear, $year) {
                $join->on($modelClass::TABLE_NAME . '.media_type_id', '=', MediaType::TABLE_NAME . '.id')
                    ->where([
                        [$seasonColumn, '=', $seasonOfYear->value],
                        [$dateColumn, '>=', $year . '-01-01'],
                        [$dateColumn, '<=', $year . '-12-31'],
                    ]);
            })
            ->groupBy('id', 'name', 'description')
            ->get();

        $copy = match ($kind) {
            UserLibraryKind::Anime => [
                'noun' => __('Anime'),
                'heading' => __('Seasonal Anime'),
                'description' => __('Browse the :x :y anime season. Join the :z community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => $seasonOfYear->key, 'y' => $year, 'z' => config('app.name')]),
                'canonicalUrl' => route('anime.seasons.year.season', [$year, $seasonOfYear->key]),
                'emptyImage' => 'empty_anime_library.webp',
                'emptyHeading' => __('No Anime'),
                'emptyDescription' => __('There are no anime airing this season.'),
            ],
            UserLibraryKind::Manga => [
                'noun' => __('Manga'),
                'heading' => __('Seasonal Manga'),
                'description' => __('Browse the :x :y manga season. Join the :z community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => $seasonOfYear->key, 'y' => $year, 'z' => config('app.name')]),
                'canonicalUrl' => route('manga.seasons.year.season', [$year, $seasonOfYear->key]),
                'emptyImage' => 'empty_manga_library.webp',
                'emptyHeading' => __('No Manga'),
                'emptyDescription' => __('There are no manga publishing this season.'),
            ],
            UserLibraryKind::Game => [
                'noun' => __('Games'),
                'heading' => __('Seasonal Games'),
                'description' => __('Browse the :x :y game season. Join the :z community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => $seasonOfYear->key, 'y' => $year, 'z' => config('app.name')]),
                'canonicalUrl' => route('games.seasons.year.season', [$year, $seasonOfYear->key]),
                'emptyImage' => 'empty_game_library.webp',
                'emptyHeading' => __('No Games'),
                'emptyDescription' => __('There are no games publishing this season.'),
            ],
        };

        return view('browse.seasons', $copy + [
            'kind' => $kind,
            'modelClass' => $modelClass,
            'year' => $year,
            'seasonOfYear' => $seasonOfYear,
            'mediaTypes' => $mediaTypes,
        ]);
    }

    /**
     * Show the archive of seasons of a kind.
     *
     * @param int $kind
     *
     * @return Application|Factory|View
     */
    public function archive(int $kind): Application|Factory|View
    {
        $copy = match ($kind) {
            UserLibraryKind::Anime => [
                'noun' => __('Anime'),
                'heading' => __('Seasonal Anime Archive'),
                'description' => __('Browse the archive of anime seasons. Join the :x community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => config('app.name')]),
                'canonicalUrl' => route('anime.seasons.archive'),
            ],
            UserLibraryKind::Manga => [
                'noun' => __('Manga'),
                'heading' => __('Seasonal Manga Archive'),
                'description' => __('Browse the archive of manga seasons. Join the :x community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => config('app.name')]),
                'canonicalUrl' => route('manga.seasons.archive'),
            ],
            UserLibraryKind::Game => [
                'noun' => __('Games'),
                'heading' => __('Seasonal Games Archive'),
                'description' => __('Browse the archive of game seasons. Join the :x community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => config('app.name')]),
                'canonicalUrl' => route('games.seasons.archive'),
            ],
        };

        return view('browse.archive', $copy + [
            'kind' => $kind,
            'modelClass' => $this->modelClass($kind),
        ]);
    }

    /**
     * The release date column of a kind.
     *
     * @param int $kind
     *
     * @return string
     */
    protected function dateColumn(int $kind): string
    {
        return match ($kind) {
            UserLibraryKind::Anime, UserLibraryKind::Manga => 'started_at',
            UserLibraryKind::Game => 'published_at',
        };
    }

    /**
     * The start date of the current season of the current year.
     *
     * @return \Carbon\Carbon
     */
    protected function seasonStartDate(): \Carbon\Carbon
    {
        return season_of_year()
            ->startDate()
            ->setYear(now()->year);
    }

    /**
     * Send the visitor to the current season of a kind.
     *
     * @param int $kind
     *
     * @return RedirectResponse
     */
    protected function redirectToSeasons(int $kind): RedirectResponse
    {
        return match ($kind) {
            UserLibraryKind::Anime => to_route('anime.seasons.index'),
            UserLibraryKind::Manga => to_route('manga.seasons.index'),
            UserLibraryKind::Game => to_route('games.seasons.index'),
        };
    }
}
