<?php

namespace App\Http\Livewire\Components;

use App\Models\Manga;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class MangaStudiosSection extends Component
{
    /**
     * The object containing the manga data.
     *
     * @var Manga $manga
     */
    public Manga $manga;

    /**
     * The array containing the studios data.
     *
     * @var array $studios
     */
    public array $studios = [];

    /**
     * The number of studios the manga has.
     *
     * @var int $studiosCount
     */
    public int $studiosCount;

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
        $this->studiosCount = $manga->studios()->count();
    }

    /**
     * Loads the media studios section.
     *
     * @return void
     */
    public function loadMangaStudios(): void
    {
        $this->studios = $this->manga->getStudios(Manga::MAXIMUM_RELATIONSHIPS_LIMIT)->items() ?? [];
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
