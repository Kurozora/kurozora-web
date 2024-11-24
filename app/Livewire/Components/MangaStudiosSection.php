<?php

namespace App\Livewire\Components;

use App\Models\Manga;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Isolate;
use Livewire\Component;

#[Isolate]
class MangaStudiosSection extends Component
{
    /**
     * The object containing the manga data.
     *
     * @var Manga $manga
     */
    public Manga $manga;

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * Prepare the component.
     *
     * @param Manga $manga
     *
     * @return void
     */
    public function mount(Manga $manga): void
    {
        $translation = $manga->translation;
        $this->manga = $manga->withoutRelations()
            ->setRelation('translation', $translation);
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
     * Loads the media studios section.
     *
     * @return Collection
     */
    public function getStudiosProperty(): Collection
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return $this->manga->studios()
            ->with('media')
            ->limit(Manga::MAXIMUM_RELATIONSHIPS_LIMIT)
            ->get();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.manga-studios-section');
    }
}
