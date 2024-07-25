<?php

namespace App\Livewire\Components\Episode;

use App\Models\Episode;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Livewire\Attributes\Isolate;
use Livewire\Component;

#[Isolate]
class UpNext extends Component
{
    /**
     * The object containing the next episode data.
     *
     * @var Episode $nextEpisode
     */
    public Episode $nextEpisode;

    /**
     * Prepare the component.
     *
     * @param Episode $nextEpisode
     *
     * @return void
     */
    public function mount(Episode $nextEpisode): void
    {
        $this->nextEpisode = $nextEpisode->load([
            'anime' => function (HasOneThrough $hasOneThrough) {
                $hasOneThrough
                    ->withoutGlobalScopes()
                    ->with([
                        'translations',
                    ]);
            },
            'season' => function ($query) {
                $query->withoutGlobalScopes()
                    ->with(['translations']);
            }
        ]);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.episode.up-next');
    }
}
