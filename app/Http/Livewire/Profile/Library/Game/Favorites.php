<?php

namespace App\Http\Livewire\Profile\Library\Game;

use App\Models\Game;
use App\Models\User;
use App\Traits\Livewire\WithGameSearch;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;

class Favorites extends Component
{
    use WithGameSearch;

    /**
     * The object containing the user data.
     *
     * @var User $user
     */
    public User $user;

    /**
     * Prepare the component.
     *
     * @param User $user
     * @return void
     */
    public function mount(User $user): void
    {
        $this->user = $user;
    }

    /**
     * Redirect the user to a random model.
     *
     * @return void
     */
    public function randomGame(): void
    {
        $game = $this->user
            ->whereFavorited(Game::class)
            ->inRandomOrder()
            ->first();
        $this->redirectRoute('games.details', $game);
    }

    /**
     * The computed search results property.
     *
     * @return ?LengthAwarePaginator
     */
    public function getSearchResultsProperty(): ?LengthAwarePaginator
    {
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

        // If no search was performed, return all games
        if (empty($this->search) && empty($wheres) && empty($orders)) {
            $games = $this->user
                ->whereFavorited(Game::class);
            return $games->paginate($this->perPage);
        }

        $gameIDs = $this->user
            ->whereFavorited(Game::class)
            ->limit(2000)
            ->pluck('favorable_id')
            ->toArray();
        $games = Game::search($this->search);
        $games->whereIn('id', $gameIDs);
        $games->wheres = $wheres;
        $games->orders = $orders;

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
        return view('livewire.profile.library.game.favorites');
    }
}
