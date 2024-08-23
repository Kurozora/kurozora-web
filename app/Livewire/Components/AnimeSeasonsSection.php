<?php

namespace App\Livewire\Components;

use App\Models\Anime;
use App\Models\Season;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Isolate;
use Livewire\Component;

#[Isolate]
class AnimeSeasonsSection extends Component
{
    /**
     * The object containing the anime data.
     *
     * @var Anime $anime
     */
    public Anime $anime;

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * Prepare the component.
     *
     * @param Anime $anime
     *
     * @return void
     */
    public function mount(Anime $anime): void
    {
        $translations = $anime->translations;
        $this->anime = $anime->withoutRelations()
            ->setRelation('translations', $translations);
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
     * Get the anime seasons.
     *
     * @return Collection
     */
    public function getSeasonsProperty(): Collection
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return $this->anime->seasons()
            ->with(['media', 'translations'])
            ->withCount(['episodes'])
            ->withAvg([
                'episodesMediaStats as rating_average' => function ($query) {
                    $query->where('rating_average', '!=', 0);
                }
            ], 'rating_average')
            ->orderBy('number', 'desc')
            ->limit(Anime::MAXIMUM_RELATIONSHIPS_LIMIT)
            ->get()
            ->map(function(Season $season) {
                return $season->setRelation('anime', $this->anime);
            });
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.anime-seasons-section');
    }
}
