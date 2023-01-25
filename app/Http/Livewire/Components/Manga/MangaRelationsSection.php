<?php

namespace App\Http\Livewire\Components\Manga;

use App\Models\Manga;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class MangaRelationsSection extends Component
{
    /**
     * The object containing the manga data.
     *
     * @var Manga $manga
     */
    public Manga $manga;

    /**
     * The array containing the manga relations data.
     *
     * @var array $mangaRelations
     */
    public array $mangaRelations = [];

    /**
     * The number of relations the manga has.
     *
     * @var int $mangaRelationsCount
     */
    public int $mangaRelationsCount;

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
        $this->mangaRelationsCount = $this->manga->mangaRelations()->count();
    }

    /**
     * Loads the manga relations section.
     *
     * @return void
     */
    public function loadMangaRelations(): void
    {
        $this->mangaRelations = $this->manga->getMangaRelations(Manga::MAXIMUM_RELATIONSHIPS_LIMIT)->items() ?? [];
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.manga.manga-relations-section');
    }
}
