<?php

namespace App\Livewire\Profile\Library\Manga;

use App\Enums\UserLibraryStatus;
use App\Models\Manga;
use App\Models\User;
use App\Models\UserLibrary;
use App\Traits\Livewire\WithMangaSearch;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;

class Index extends Component
{
    use WithMangaSearch;

    /**
     * The object containing the user data.
     *
     * @var User $user
     */
    public User $user;

    /**
     * The status of the library.
     *
     * @var string $status
     */
    public string $status = '';

    /**
     * The query strings of the component.
     *
     * @var string[] $queryString
     */
    protected $queryString = [
        'status',
    ];

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * Prepare the component.
     *
     * @param User $user
     * @return void
     */
    public function mount(User $user): void
    {
        $this->user = $user;

        $status = str($this->status)->title();
        $status = str_replace('-', '', $status);

        if (!UserLibraryStatus::hasKey($status)) {
            $this->status = 'reading';
        }
    }

    /**
     * Sets the property to load the page.
     *
     * @return void
     */
    public function loadPage(): void
    {
        $this->readyToLoad = true;
    }

    /**
     * Redirect the user to a random model.
     *
     * @return void
     * @throws InvalidEnumKeyException
     * @throws InvalidEnumMemberException
     */
    public function randomManga(): void
    {
        // Get library status
        $status = str_replace('-', '', $this->status);
        $userLibraryStatus = UserLibraryStatus::fromKey($status);

        $manga = $this->user
            ->whereTracked(Manga::class)
            ->wherePivot('status', $userLibraryStatus->value)
            ->inRandomOrder()
            ->first();
        $this->redirectRoute('manga.details', $manga);
    }

    /**
     * The computed search results property.
     *
     * @return ?LengthAwarePaginator
     * @throws InvalidEnumKeyException
     * @throws InvalidEnumMemberException
     */
    public function getSearchResultsProperty(): ?LengthAwarePaginator
    {
        if (!$this->readyToLoad) {
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
        $whereIns = [];
        foreach ($this->filter as $attribute => $filter) {
            $attribute = str_replace(':', '.', $attribute);
            $selected = $filter['selected'];
            $type = $filter['type'];

            if ((is_numeric($selected) && $selected >= 0) || !empty($selected)) {
                if ($type === 'multiselect') {
                    $whereIns[$attribute] = $selected;
                } else {
                    $wheres[$attribute] = match ($type) {
                        'date' => Carbon::createFromFormat('Y-m-d', $selected)
                            ?->setTime(0, 0)
                            ->timestamp,
                        'time' => $selected . ':00',
                        'double' => number_format($selected, 2, '.', ''),
                        default => $selected,
                    };
                }
            }
        }

        // Get library status
        $upperCaseStatus = implode('-', array_map('ucfirst', explode('-', $this->status)));
        $status = str_replace('-', '', $upperCaseStatus);
        $userLibraryStatus = UserLibraryStatus::fromKey($status);

        // If no search was performed, return all manga
        if (empty($this->search) && empty($wheres) && empty($whereIns)) {
            $mangas = $this->user
                ->whereTracked(Manga::class)
                ->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating'])
                ->when(auth()->user(), function ($query, $user) {
                    $query->with(['library' => function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    }]);
                })
                ->wherePivot('status', $userLibraryStatus->value);
            return $mangas->paginate($this->perPage);
        }

        // Search
        $mangaIDs = collect(UserLibrary::search($this->search)
            ->where('user_id', $this->user->id)
            ->where('trackable_type', addslashes(Manga::class))
            ->where('status', $userLibraryStatus->value)
            ->simplePaginateRaw(perPage: 2000, page: 1)
            ->items()['hits'] ?? [])
            ->pluck('trackable_id')
            ->toArray();
        $whereIns['id'] = $mangaIDs;

        $mangas = Manga::search($this->search);
        $mangas->orders = $orders;
        $mangas->wheres = $wheres;
        $mangas->whereIns = $whereIns;

        $mangas->query(function (Builder $query) {
            $query->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating'])
                ->when(auth()->user(), function ($query, $user) {
                    $query->with(['library' => function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    }]);
                });
        });

        // Paginate
        return $mangas->paginate($this->perPage);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.library.manga.index');
    }
}
