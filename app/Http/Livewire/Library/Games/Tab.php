<?php

namespace App\Http\Livewire\Library\Games;

use App\Enums\UserLibraryStatus;
use App\Models\Game;
use App\Models\User;
use App\Models\UserLibrary;
use App\Traits\Livewire\WithGameSearch;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;

class Tab extends Component
{
    use WithGameSearch;

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
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

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
     * Sets the property to load the section.
     *
     * @return void
     */
    public function loadSection(): void
    {
        $this->readyToLoad = true;
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
    public function randomGame(): void
    {
        // Get library status
        $status = str_replace('-', '', $this->status);
        $userLibraryStatus = UserLibraryStatus::fromKey($status);

        $game = $this->user->whereTracked(Game::class)
            ->wherePivot('status', $userLibraryStatus->value)
            ->inRandomOrder()
            ->first();
        $this->redirectRoute('games.details', $game);
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
        if (!$this->loadResourceIsEnabled || !$this->readyToLoad) {
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

        // If no search was performed, return all game
        if (empty($this->search) && empty($wheres) && empty($orders)) {
            $games = $this->user
                ->whereTracked(Game::class)
                ->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating'])
                ->wherePivot('status', $userLibraryStatus->value);
            return $games->paginate($this->perPage);
        }

        // Search
        $gameIDs = collect(UserLibrary::search($this->search)
            ->where('user_id', $this->user->id)
            ->where('trackable_type', Game::class)
            ->where('status', $userLibraryStatus->value)
            ->simplePaginateRaw(perPage: 2000, page: 1)
            ->items()['hits'] ?? [])
            ->pluck('trackable_id')
            ->toArray();
        $games = Game::search($this->search);
        $games->whereIn('id', $gameIDs);
        $games->wheres = $wheres;
        $games->orders = $orders;
        $games->query(function (Builder $query) {
            $query->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating']);
        });

        // Paginate
        return $games->paginate($this->perPage);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.library.games.tab');
    }
}
