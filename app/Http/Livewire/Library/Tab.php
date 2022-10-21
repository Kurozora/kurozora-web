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
use App\Models\User;
use App\Models\UserLibrary;
use App\Traits\Livewire\WithSearch;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
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
     * The object containing the user data.
     *
     * @var User $user
     */
    public User $user;

    /**
     * The user library status string.
     *
     * @var string $status
     */
    public string $status;

    /**
     * Whether to load the resource.
     *
     * @var bool $loadResourceIsEnabled
     */
    public bool $loadResourceIsEnabled = false;

    /**
     * Prepare the component.
     *
     * @param User $user
     * @param string $status
     * @return void
     */
    public function mount(User $user, string $status): void
    {
        $status = str($status)->title();
        $this->user = $user;
        $this->status = $status;
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
        $status = str_replace('-', '', $this->status);
        $userLibraryStatus = UserLibraryStatus::fromKey($status);

        $anime = $this->user
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
                $orders[] = [
                    'column' => $attribute,
                    'direction' => $selected,
                ];
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
        $status = str_replace('-', '', $this->status);
        $userLibraryStatus = UserLibraryStatus::fromKey($status);

        // If no search was performed, return all anime
        if (empty($this->search) && empty($wheres) && empty($orders)) {
            $animes = $this->user
                ->library()
                ->wherePivot('status', $userLibraryStatus->value);
            return $animes->paginate($this->perPage);
        }

        // Search
        $animeIDs = collect(UserLibrary::search($this->search)
            ->where('user_id', $this->user->id)
            ->where('status', $userLibraryStatus->value)
            ->paginate(perPage: 2000, page: 1)
            ->items())
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
