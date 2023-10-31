<?php

namespace App\Http\Livewire\Components;

use App\Models\Anime;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class AnimeCastSection extends Component
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
     * Loads the anime cast section.
     *
     * @return Collection
     */
    public function getAnimeCastProperty(): Collection
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return $this->anime->cast()
            ->with([
                'person' => function ($query) {
                    $query->with(['media']);
                },
                'character' => function ($query) {
                    $query->with(['media', 'translations']);
                },
                'castRole'
            ])
            ->limit(Anime::MAXIMUM_RELATIONSHIPS_LIMIT)
            ->get();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.anime-cast-section');
    }
}
