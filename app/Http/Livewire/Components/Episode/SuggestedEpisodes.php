<?php

namespace App\Http\Livewire\Components\Episode;

use App\Models\Episode;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class SuggestedEpisodes extends Component
{
    /**
     * The episode's title.
     *
     * @var string $title
     */
    public string $title;

    /**
     * The next episode's ID.
     *
     * @var null|string|int $nextEpisodeID
     */
    public null|string|int $nextEpisodeID;

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * Prepare the component.
     *
     * @param string $title
     * @param null|string|int $nextEpisodeId
     * @return void
     */
    public function mount(string $title, null|string|int $nextEpisodeId): void
    {
        $this->title = $title;
        // ID written as `Id`, so the component parameter doesn't
        // become `next-episode-i-d`.
        $this->nextEpisodeID = $nextEpisodeId;
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
     * A list of episode suggestions.
     *
     * @return Collection
     */
    public function getEpisodesProperty(): Collection
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return Episode::search(mb_convert_encoding(substr($this->title, 0, 20), 'UTF-8', mb_list_encodings()))
            ->take(10)
            ->query(function ($query) {
                $query->with([
                    'anime' => function ($query) {
                        $query->with(['translations']);
                    },
                    'media',
                    'season' => function ($query) {
                        $query->with(['translations']);
                    },
                    'translations'
                ]);
            })
            ->get();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.episode.suggested-episodes');
    }
}
