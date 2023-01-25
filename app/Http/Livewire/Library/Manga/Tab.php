<?php

namespace App\Http\Livewire\Library\Manga;

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
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;

class Tab extends Component
{
    use WithMangaSearch;

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
     * @throws InvalidEnumMemberException
     */
    public function randomManga(): void
    {
        // Get library status
        $status = str_replace('-', '', $this->status);
        $userLibraryStatus = UserLibraryStatus::fromKey($status);

        $manga = $this->user->whereTracked(Manga::class)
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

        // If no search was performed, return all manga
        if (empty($this->search) && empty($wheres) && empty($orders)) {
            $mangas = $this->user->whereTracked(Manga::class)
                ->wherePivot('status', $userLibraryStatus->value);
            return $mangas->paginate($this->perPage);
        }

        // Search
        $mangaIDs = collect(UserLibrary::search($this->search)
            ->where('user_id', $this->user->id)
            ->where('trackable_type', Manga::class)
            ->where('status', $userLibraryStatus->value)
            ->paginate(perPage: 2000, page: 1)
            ->items()
        )
            ->pluck('trackable_id')
            ->toArray();
        $mangas = Manga::search($this->search);
        $mangas->whereIn('id', $mangaIDs);
        $mangas->wheres = $wheres;
        $mangas->orders = $orders;

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
        return view('livewire.library.manga.tab');
    }
}
