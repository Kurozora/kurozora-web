<?php

namespace App\Livewire\Components\Episode;

use App\Traits\Livewire\WithPagination;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;

class PastEpisodesSection extends Component
{
    use WithPagination;

    /**
     * Determines whether to load the page.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * The component's listeners.
     *
     * @var array
     */
    protected $listeners = [
        'refresh-past-episodes' => '$refresh',
    ];

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount(): void
    {
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
     * Returns the list of past episodes.
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getEpisodesProperty(): Collection|LengthAwarePaginator
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return auth()->user()?->past_episodes()
            ->when(auth()->user(), function ($query, $user) {
                $query->withExists([
                    'user_watched_episodes as isWatched' => function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    },
                ]);
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
        return view('livewire.components.episode.past-episodes-section');
    }
}
