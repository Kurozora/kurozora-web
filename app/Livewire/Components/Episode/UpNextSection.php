<?php

namespace App\Livewire\Components\Episode;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class UpNextSection extends Component
{
    /**
     * The component's listeners.
     *
     * @var array
     */
    protected $listeners = [
        'refresh-up-next-section' => '$refresh',
    ];

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount()
    {
    }

    /**
     * Returns the list of up-next episodes.
     *
     * @return Collection|array
     */
    public function getEpisodesProperty(): Collection|array
    {
        return auth()->user()?->up_next_episodes()
            ->limit(10)
            ->get() ?? [];
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.episode.up-next-section');
    }
}
