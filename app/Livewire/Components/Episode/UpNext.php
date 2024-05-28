<?php

namespace App\Livewire\Components\Episode;

use App\Models\Episode;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Collection;
use Livewire\Attributes\Isolate;
use Livewire\Component;

#[Isolate]
class UpNext extends Component
{
    /**
     * The object containing the episode data.
     *
     * @var Episode $episode
     */
    public Episode $episode;

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * Prepare the component.
     *
     * @param Episode $episode
     * @return void
     */
    public function mount(Episode $episode): void
    {
        $this->episode = $episode->withoutRelations();
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
     * Get the next episode.
     *
     * @return Collection
     */
    public function getNextEpisodeProperty(): Collection
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return $this->episode
            ->next_episode()
            ->with([
                'anime' => function (HasOneThrough $hasOneThrough) {
                    $hasOneThrough->with([
                        'translations',
                    ]);
                },
                'media',
                'season' => function ($query) {
                    $query->with(['translations']);
                },
                'translations'
            ])
            ->get();
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
