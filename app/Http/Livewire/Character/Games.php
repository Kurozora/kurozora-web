<?php

namespace App\Http\Livewire\Character;

use App\Models\Character;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class Games extends Component
{
    use WithPagination;

    /**
     * The object containing the character data.
     *
     * @var Character $character
     */
    public Character $character;

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * Prepare the component.
     *
     * @param Character $character
     *
     * @return void
     */
    public function mount(Character $character): void
    {
        $this->character = $character->load(['media']);
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
     * Get the games property.
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getGamesProperty(): Collection|LengthAwarePaginator
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return $this->character->games()
            ->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating'])
            ->when(auth()->user(), function ($query, $user) {
                $query->with(['library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            })
            ->paginate(25);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.character.games');
    }
}
