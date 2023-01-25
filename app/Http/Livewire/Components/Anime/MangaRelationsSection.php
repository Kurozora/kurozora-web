<?php

namespace App\Http\Livewire\Components\Anime;

use App\Models\Anime;
use App\Models\Manga;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class MangaRelationsSection extends Component
{
    /**
     * The object containing the anime data.
     *
     * @var Anime $anime
     */
    public Anime $anime;

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
     * @param Anime $anime
     *
     * @return void
     */
    public function mount(Anime $anime): void
    {
        $this->anime = $anime;
        $this->mangaRelationsCount = $this->anime->mangaRelations()->count();
    }

    /**
     * Loads the manga relations section.
     *
     * @return void
     */
    public function loadMangaRelations(): void
    {
        $this->mangaRelations = $this->anime->getMangaRelations(Manga::MAXIMUM_RELATIONSHIPS_LIMIT)->items() ?? [];
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.anime.manga-relations-section');
    }
}
