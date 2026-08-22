<?php

namespace App\Livewire;

use App\Enums\AdaptedAnimeFilter;
use App\Enums\UserLibraryKind;
use Closure;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Laravel\Scout\Builder as ScoutBuilder;

class Adapted extends Catalog
{
    /**
     * The selected anime-adaptation airing filter.
     *
     * @var string $adaptation
     */
    public string $adaptation = 'airing';

    /**
     * The ids of items adapted to anime for the active filter.
     *
     * @var int[]|null $adaptedIdsCache
     */
    protected ?array $adaptedIdsCache = null;

    /**
     * The query strings of the component.
     *
     * @return array
     */
    protected function queryString(): array
    {
        return array_merge(parent::queryString(), [
            'adaptation' => ['as' => 'show', 'except' => strtolower(AdaptedAnimeFilter::getKey(AdaptedAnimeFilter::Airing))],
        ]);
    }

    /**
     * Called when the `adaptation` property is updated.
     *
     * @return void
     */
    public function updatedAdaptation(): void
    {
        $this->adaptedIdsCache = null;
        $this->resetPage();
    }

    /**
     * Redirects the user to a random adapted item of the active kind.
     *
     * @return void
     */
    public function randomItem(): void
    {
        $modelClass = $this->modelClass();
        $item = $modelClass::adaptedToAnime($this->adaptationFilter())
            ->inRandomOrder()
            ->first();

        if ($item === null) {
            return;
        }

        match ($this->kind) {
            UserLibraryKind::Anime => $this->redirectRoute('anime.details', $item),
            UserLibraryKind::Manga => $this->redirectRoute('manga.details', $item),
            UserLibraryKind::Game => $this->redirectRoute('games.details', $item),
        };
    }

    /**
     * Build a 'search index' query for the given resource.
     *
     * @param EloquentBuilder $query
     *
     * @return EloquentBuilder
     */
    public function searchIndexQuery(EloquentBuilder $query): EloquentBuilder
    {
        return parent::searchIndexQuery($query->whereKey($this->adaptedIds()));
    }

    /**
     * Build a 'search' query for the given resource.
     *
     * @param ScoutBuilder $query
     *
     * @return ScoutBuilder
     */
    public function searchQuery(ScoutBuilder $query): ScoutBuilder
    {
        return parent::searchQuery($query->whereIn('id', $this->adaptedIds()));
    }

    /**
     * Searches the user's library within the adapted item set.
     *
     * @param string       $modelClass
     * @param int          $userId
     * @param array        $statuses
     * @param bool         $excludeHidden
     * @param array        $wheres
     * @param array        $whereIns
     * @param array        $orders
     * @param null|Closure $hydrate
     *
     * @return LengthAwarePaginator
     */
    protected function paginateLibraryScopedSearch(
        string   $modelClass,
        int      $userId,
        array    $statuses = [],
        bool     $excludeHidden = false,
        array    $wheres = [],
        array    $whereIns = [],
        array    $orders = [],
        ?Closure $hydrate = null,
    ): LengthAwarePaginator
    {
        $adaptedIds = $this->adaptedIds();
        $whereIns['id'] = $adaptedIds;

        return parent::paginateLibraryScopedSearch(
            modelClass: $modelClass,
            userId: $userId,
            statuses: $statuses,
            excludeHidden: $excludeHidden,
            wheres: $wheres,
            whereIns: $whereIns,
            orders: $orders,
            hydrate: function (EloquentBuilder $query) use ($adaptedIds) {
                $query->whereKey($adaptedIds)
                    ->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating'])
                    ->when(auth()->user(), function ($query, $user) {
                        $query->with(['library' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        }]);
                    });
            },
        );
    }

    /**
     * The ids of items adapted to anime for the active filter.
     *
     * @return int[]
     */
    protected function adaptedIds(): array
    {
        $modelClass = $this->modelClass();

        return $this->adaptedIdsCache ??= $modelClass::adaptedToAnime($this->adaptationFilter())
            ->pluck($modelClass::TABLE_NAME . '.id')
            ->all();
    }

    /**
     * The active adaptation filter.
     *
     * @return AdaptedAnimeFilter
     */
    protected function adaptationFilter(): AdaptedAnimeFilter
    {
        $key = ucfirst($this->adaptation);

        return AdaptedAnimeFilter::hasKey($key)
            ? AdaptedAnimeFilter::fromKey($key)
            : AdaptedAnimeFilter::Airing();
    }

    /**
     * Returns the localized noun used in og:title and document title.
     *
     * @return string
     */
    public function getOgTitleNounProperty(): string
    {
        return $this->getHeadingProperty();
    }

    /**
     * Returns the og:description and meta description.
     *
     * @return string
     */
    public function getOgDescriptionProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Game => __('Discover games with anime adaptations airing now or slated to premiere on :x.', ['x' => config('app.name')]),
            default => __('Discover manga with anime adaptations airing now or slated to premiere on :x.', ['x' => config('app.name')]),
        };
    }

    /**
     * Returns the canonical URL.
     *
     * @return string
     */
    public function getCanonicalUrlProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Game => route('games.adapted'),
            default => route('manga.adapted'),
        };
    }

    /**
     * Returns the heading shown above the list.
     *
     * @return string
     */
    public function getHeadingProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Game => __('Games Adapted to Anime'),
            default => __('Manga Adapted to Anime'),
        };
    }

    /**
     * Returns the URL of the parent index page.
     *
     * @return string
     */
    public function getParentUrlProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Game => route('games.index'),
            default => route('manga.index'),
        };
    }

    /**
     * Returns the label of the parent index page.
     *
     * @return string
     */
    public function getParentLabelProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Game => __('Games'),
            default => __('Manga'),
        };
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.adapted');
    }
}
