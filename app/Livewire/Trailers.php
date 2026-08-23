<?php

namespace App\Livewire;

use App\Enums\TrailerSort;
use App\Enums\UserLibraryKind;
use App\Models\Video;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class Trailers extends Catalog
{
    /**
     * The number of days a video counts as a recent release.
     */
    protected const int TRENDING_DAYS = 30;

    /**
     * The selected trailer sort.
     *
     * @var string $sort
     */
    public string $sort = 'just-added';

    /**
     * The ids of the titles the active search and filters match.
     *
     * @var int[]|null $parentIdsCache
     */
    protected ?array $parentIdsCache = null;

    /**
     * The number of videos the hero section rotates through.
     */
    protected const int HERO_SIZE = 8;

    /**
     * The videos given the prominent placement.
     *
     * @var Collection|null $heroCache
     */
    protected ?Collection $heroCache = null;

    /**
     * The query strings of the component.
     *
     * @return array
     * @throws InvalidEnumMemberException
     */
    protected function queryString(): array
    {
        return array_merge(parent::queryString(), [
            'sort' => ['except' => Str::kebab(TrailerSort::getKey(TrailerSort::JustAdded))],
        ]);
    }

    /**
     * Called when the `sort` property is updated.
     *
     * @return void
     */
    public function updatedSort(): void
    {
        $this->heroCache = null;
        $this->resetPage();
    }

    /**
     * Redirects the user to a random title with a trailer.
     *
     * @return void
     */
    public function randomItem(): void
    {
        $video = $this->videoQuery()
            ->inRandomOrder()
            ->first();

        if ($video?->videoable === null) {
            return;
        }

        match ($this->kind) {
            UserLibraryKind::Game => $this->redirectRoute('games.details', $video->videoable),
            default => $this->redirectRoute('anime.details', $video->videoable),
        };
    }

    /**
     * The computed search results property.
     *
     * @return ?LengthAwarePaginator
     */
    public function getSearchResultsProperty(): ?LengthAwarePaginator
    {
        if (!$this->readyToLoad) {
            return null;
        }

        $query = $this->videoQuery();
        $heroKeys = $this->getHeroVideosProperty()
            ->modelKeys();

        if (!empty($heroKeys)) {
            $query->whereKeyNot($heroKeys);
        }

        return $this->applySort($query)
            ->paginate($this->perPage);
    }

    /**
     * The videos the hero section rotates through.
     *
     * @return Collection
     */
    public function getHeroVideosProperty(): Collection
    {
        if (!$this->readyToLoad) {
            return new Collection();
        }

        if ($this->heroCache === null) {
            $this->heroCache = $this->applySort($this->videoQuery())
                ->take(self::HERO_SIZE)
                ->get();
        }

        return $this->heroCache;
    }

    /**
     * Builds the query of the videos the active kind, type, search, and filters allow.
     *
     * @return EloquentBuilder
     */
    protected function videoQuery(): EloquentBuilder
    {
        $modelClass = $this->modelClass();
        $user = auth()->user();

        return Video::whereHasMorph('videoable', [$modelClass], function (EloquentBuilder $query) {
            $this->constrainParent($query);
        })
            ->whereIn(Video::TABLE_NAME . '.id', $this->representativeVideoIds())
            ->with(['videoable' => function (MorphTo $morphTo) use ($modelClass, $user) {
                $morphTo->morphWith([
                    $modelClass => [
                        'genres',
                        'media',
                        'translation',
                        'library' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user?->id);
                        },
                    ],
                ]);
            }]);
    }

    /**
     * Limits the parent titles to the ones the active sort, search, and filters allow.
     *
     * @param EloquentBuilder $query
     *
     * @return void
     */
    protected function constrainParent(EloquentBuilder $query): void
    {
        $modelClass = $this->modelClass();

        if ($this->sortValue() === TrailerSort::MostAnticipated) {
            $query->whereDate($modelClass::TABLE_NAME . '.' . $this->releaseDateColumn(), '>', today());
        }

        $parentIds = $this->parentIds();

        if ($parentIds !== null) {
            $query->whereIn($modelClass::TABLE_NAME . '.id', $parentIds);
        }
    }

    /**
     * The ids of the one video that stands in for each title.
     *
     * @return EloquentBuilder
     */
    protected function representativeVideoIds(): EloquentBuilder
    {
        $ranked = Video::selectRaw('id, ROW_NUMBER() OVER (PARTITION BY videoable_id ORDER BY published_at DESC, id DESC) AS ranking')
            ->where('videoable_type', '=', $this->modelClass());

        return Video::withoutGlobalScopes()
            ->select('id')
            ->fromSub($ranked, 'ranked')
            ->where('ranking', '=', 1);
    }

    /**
     * Limits and orders the given query the way the active sort presents videos.
     *
     * @param EloquentBuilder $query
     *
     * @return EloquentBuilder
     */
    protected function applySort(EloquentBuilder $query): EloquentBuilder
    {
        $tableName = Video::TABLE_NAME;
        $publishedAt = $tableName . '.published_at';

        if ($this->sortValue() === TrailerSort::Trending) {
            $query->whereBetween($publishedAt, [today()->subDays(self::TRENDING_DAYS), now()]);
        }

        match ($this->sortValue()) {
            TrailerSort::JustAdded => $query->orderByDesc($publishedAt)
                ->orderByDesc($tableName . '.id'),
            default => $query->orderByDesc($tableName . '.view_count')
                ->orderByDesc($tableName . '.id'),
        };

        return $query;
    }

    /**
     * The ids of the titles the active search and filters match.
     *
     * @return int[]|null
     */
    protected function parentIds(): ?array
    {
        if (!$this->isSearching()) {
            return null;
        }

        if ($this->parentIdsCache !== null) {
            return $this->parentIdsCache;
        }

        $modelClass = $this->modelClass();
        $wheres = [];
        $whereIns = [];

        foreach ($this->filter as $attribute => $filter) {
            if ($attribute == 'library_status') {
                continue;
            }

            $attribute = str_replace(':', '.', $attribute);
            $selected = $filter['selected'];

            if ((is_numeric($selected) && $selected >= 0) || !empty($selected)) {
                if ($filter['type'] === 'multiselect') {
                    $whereIns[$attribute] = $selected;
                } else {
                    $wheres[$attribute] = match ($filter['type']) {
                        'date' => \Carbon\Carbon::createFromFormat('Y-m-d', $selected)
                            ?->setTime(0, 0)
                            ->timestamp,
                        'time' => $selected . ':00',
                        'double' => number_format($selected, 2, '.', ''),
                        default => $selected,
                    };
                }
            }
        }

        if (empty($this->search) && empty($wheres) && empty($whereIns)) {
            return $this->parentIdsCache = $modelClass::query()
                ->when(!empty($this->typeValue), function (EloquentBuilder $query) {
                    $query->where($this->typeColumn(), '=', $this->typeValue);
                })
                ->when(!empty($this->letter), function (EloquentBuilder $query) {
                    if ($this->letter == '.') {
                        $query->whereRaw($this->letterIndexColumn() . ' REGEXP \'^[^a-zA-Z]*$\'');
                    } else {
                        $query->whereLike($this->letterIndexColumn(), $this->letter . '%');
                    }
                })
                ->pluck($modelClass::TABLE_NAME . '.id')
                ->all();
        }

        if (!empty($this->letter)) {
            $wheres['letter'] = $this->letter;
        }

        if (!empty($this->typeValue)) {
            $wheres[$this->typeColumn()] = $this->typeValue;
        }

        $search = $modelClass::search($this->search);
        $search->wheres = $wheres;
        $search->whereIns = $whereIns;

        return $this->parentIdsCache = $search->take($modelClass::count())
            ->keys()
            ->all();
    }

    /**
     * Returns the release date column of the active kind.
     *
     * @return string
     */
    protected function releaseDateColumn(): string
    {
        return $this->kind === UserLibraryKind::Game ? 'published_at' : 'started_at';
    }

    /**
     * The value of the active trailer sort.
     *
     * @return int
     */
    protected function sortValue(): int
    {
        $key = Str::studly($this->sort);

        return TrailerSort::hasKey($key)
            ? TrailerSort::getValue($key)
            : TrailerSort::JustAdded;
    }

    /**
     * The selectable sort options.
     *
     * @return array
     */
    public function getSortOptionsProperty(): array
    {
        return [
            Str::kebab(TrailerSort::getKey(TrailerSort::JustAdded)) => __('Just Added'),
            Str::kebab(TrailerSort::getKey(TrailerSort::Trending)) => __('Trending'),
            Str::kebab(TrailerSort::getKey(TrailerSort::MostPopular)) => __('Most Popular'),
            Str::kebab(TrailerSort::getKey(TrailerSort::MostAnticipated)) => __('Most Anticipated'),
        ];
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
            UserLibraryKind::Game => __('Watch game trailers, teasers and promotional videos on :x. Discover what is coming next and add it to your library.', ['x' => config('app.name')]),
            default => __('Watch anime trailers, teasers and promotional videos on :x. Discover what is coming next and add it to your library.', ['x' => config('app.name')]),
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
            UserLibraryKind::Game => route('games.trailers'),
            default => route('anime.trailers'),
        };
    }

    /**
     * Returns the app argument used for the deep link.
     *
     * @return string
     */
    public function getAppArgumentProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Game => 'games/trailers',
            default => 'anime/trailers',
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
            UserLibraryKind::Game => __('Game Trailers'),
            default => __('Anime Trailers'),
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
            default => route('anime.index'),
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
            default => __('Anime'),
        };
    }

    /**
     * Returns the empty-state heading.
     *
     * @return string
     */
    public function getEmptyHeadingProperty(): string
    {
        return __('Trailers Not Found');
    }

    /**
     * Returns the empty-state body copy.
     *
     * @return string
     */
    public function getEmptyDescriptionProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Game => __('No game trailers found with the selected criteria.'),
            default => __('No anime trailers found with the selected criteria.'),
        };
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.trailers');
    }
}
