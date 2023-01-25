<?php

namespace App\Http\Livewire\Components;

use App\Models\Manga;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class MangaCastSection extends Component
{
    /**
     * The object containing the manga data.
     *
     * @var Manga $manga
     */
    public Manga $manga;

    /**
     * The array containing the cast data.
     *
     * @var array $mangaCast
     */
    public array $mangaCast = [];

    /**
     * The number of cast the manga has.
     *
     * @var int $castCount
     */
    public int $castCount;

    /**
     * Prepare the component.
     *
     * @param Manga $manga
     *
     * @return void
     */
    public function mount(Manga $manga): void
    {
        $this->manga = $manga;
        $this->castCount = $manga->cast()->count();
    }

    /**
     * Loads the manga cast section.
     *
     * @return void
     */
    public function loadMangaCast(): void
    {
        $this->mangaCast = $this->manga->getCast(Manga::MAXIMUM_RELATIONSHIPS_LIMIT)->items() ?? [];
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.manga-cast-section');
    }
}
