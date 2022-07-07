<?php

namespace App\Http\Livewire\Library;

use App\Enums\DayOfWeek;
use App\Enums\SeasonOfYear;
use App\Enums\UserLibraryStatus;
use App\Models\Anime;
use App\Models\MediaType;
use App\Models\Source;
use App\Models\Status;
use App\Models\TvRating;
use App\Models\UserLibrary;
use App\Traits\Livewire\WithSearch;
use Auth;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Tab extends Component
{
    use WithSearch;

    /**
     * The model used for searching.
     *
     * @var string $searchModel
     */
    public static string $searchModel = Anime::class;

    /**
     * The user library status string.
     *
     * @var string $userLibraryStatusString
     */
    public string $userLibraryStatusString;

    /**
     * Whether to load the resource.
     *
     * @var bool $loadResourceIsEnabled
     */
    public bool $loadResourceIsEnabled = false;

    /**
     * Prepare the component.
     *
     * @param UserLibraryStatus $userLibraryStatus
     * @return void
     */
    public function mount(UserLibraryStatus $userLibraryStatus): void
    {
        $this->userLibraryStatusString = $userLibraryStatus->key;
        $this->setFilterableAttributes();
        $this->setOrderableAttributes();
    }

    /**
     * Enable resource loading.
     *
     * @return void
     */
    public function loadResource(): void
    {
        $this->loadResourceIsEnabled = true;
    }

    /**
     * Redirect the user to a random model.
     *
     * @return void
     * @throws InvalidEnumKeyException
     */
    public function randomAnime(): void
    {
        // Get library status
        $userLibraryStatus = UserLibraryStatus::fromKey($this->userLibraryStatusString);

        $anime = Auth::user()
            ->library()
            ->wherePivot('status', $userLibraryStatus->value)
            ->inRandomOrder()
            ->first();
        $this->redirectRoute('anime.details', $anime);
    }

    /**
     * The computed search results property.
     *
     * @return ?LengthAwarePaginator
     * @throws InvalidEnumKeyException
     */
    public function getSearchResultsProperty(): ?LengthAwarePaginator
    {
        if (!$this->loadResourceIsEnabled) {
            return null;
        }

        // Order
        $orders = [];
        foreach ($this->order as $attribute => $order) {
            $attribute = str_replace(':', '.', $attribute);
            $selected = $order['selected'];

            if (!empty($selected)) {
                $orders[$attribute] = $selected;
            }
        }

        // Filter
        $wheres = [];
        foreach ($this->filter as $attribute => $filter) {
            $attribute = str_replace(':', '.', $attribute);
            $selected = $filter['selected'];
            $type = $filter['type'];

            if ((is_numeric($selected) && $selected >= 0) || !empty($selected)) {
                $wheres[$attribute] = match ($type) {
                    'date' => Carbon::createFromFormat('Y-m-d', $selected)
                        ->setTime(0, 0)
                        ->timestamp,
                    'time' => $selected . ':00',
                    default => $selected,
                };
            }
        }

        // Get library status
        $userLibraryStatus = UserLibraryStatus::fromKey($this->userLibraryStatusString);

        // If no search was performed, return all anime
        if (empty($this->search) && empty($wheres) && empty($orders)) {
            $animes = Auth::user()
                ->library()
                ->wherePivot('status', $userLibraryStatus->value);
            return $animes->paginate($this->perPage);
        }

        // Search
        $animeIDs = UserLibrary::where('user_id', Auth::user()->id)
            ->where('status', $userLibraryStatus->value)
            ->pluck('anime_id')
            ->toArray();
        $animes = Anime::search($this->search);
        $animes->whereIn('id', $animeIDs);
        $animes->wheres = $wheres;
        $animes->orders = $orders;

        // Paginate
        return $animes->paginate($this->perPage);
    }

    /**
     * Set the orderable attributes of the model.
     *
     * @return void
     */
    public function setOrderableAttributes(): void
    {
        $this->order = [
            'title' => [
                'title' => __('Title'),
                'options' => [
                    'Default' => null,
                    'A-Z' => 'asc',
                    'Z-A' => 'desc',
                ],
                'selected' => null,
            ],
            'first_aired' => [
                'title' => __('First Aired'),
                'options' => [
                    'Default' => null,
                    'Newest' => 'desc',
                    'Oldest' => 'asc',
                ],
                'selected' => null,
            ],
            'last_aired' => [
                'title' => __('Last Aired'),
                'options' => [
                    'Default' => null,
                    'Newest' => 'desc',
                    'Oldest' => 'asc',
                ],
                'selected' => null,
            ],
            'duration' => [
                'title' => __('Duration'),
                'options' => [
                    'Default' => null,
                    'Shortest' => 'asc',
                    'Longest' => 'desc',
                ],
                'selected' => null,
            ],
        ];
    }

    /**
     * Set the filterable attributes of the model.
     *
     * @return void
     */
    public function setFilterableAttributes(): void
    {
        $this->filter = [
            'first_aired' => [
                'title' => __('First Aired'),
                'type' => 'date',
                'selected' => null,
            ],
            'last_aired' => [
                'title' => __('Last Aired'),
                'type' => 'date',
                'selected' => null,
            ],
            'duration' => [
                'title' => __('Duration (seconds)'),
                'type' => 'duration',
                'selected' => null,
            ],
            'tv_rating_id' => [
                'title' => __('TV Rating'),
                'type' => 'select',
                'options' => TvRating::all()->pluck('name', 'id'),
                'selected' => null,
            ],
            'media_type_id' => [
                'title' => __('Media Type'),
                'type' => 'select',
                'options' => MediaType::where('type', 'anime')->pluck('name', 'id'),
                'selected' => null,
            ],
            'source_id' => [
                'title' => __('Source'),
                'type' => 'select',
                'options' => Source::all()->pluck('name', 'id'),
                'selected' => null,
            ],
            'status_id' => [
                'title' => __('Airing Status'),
                'type' => 'select',
                'options' => Status::where('type', 'anime')->pluck('name', 'id'),
                'selected' => null,
            ],
            'air_time' => [
                'title' => __('Air Time'),
                'type' => 'time',
                'selected' => null,
            ],
            'air_day' => [
                'title' => __('Air Day'),
                'type' => 'select',
                'options' => DayOfWeek::asSelectArray(),
                'selected' => null,
            ],
            'air_season' => [
                'title' => __('Air Season'),
                'type' => 'select',
                'options' => SeasonOfYear::asSelectArray(),
                'selected' => null,
            ]
        ];

        if (settings('tv_rating') >= 4) {
            $this->filter['is_nsfw'] = [
                'title' => __('NSFW'),
                'type' => 'bool',
                'options' => [
                    __('Shown'),
                    __('Hidden'),
                ],
                'selected' => null,
            ];
        }
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.library.tab');
    }
}
