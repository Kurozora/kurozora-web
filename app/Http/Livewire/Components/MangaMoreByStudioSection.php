<?php

namespace App\Http\Livewire\Components;

use App\Models\Studio;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class MangaMoreByStudioSection extends Component
{
    /**
     * The object containing the studio data.
     *
     * @var Studio
     */
    public Studio $studio;

    /**
     * The array containing the more by studio data.
     *
     * @var array $moreByStudio
     */
    public array $moreByStudio = [];

    /**
     * The number of manga the studio has.
     *
     * @var int $moreByStudioCount
     */
    public int $moreByStudioCount;

    /**
     * Prepare the component.
     *
     * @param Studio $studio
     *
     * @return void
     */
    public function mount(Studio $studio): void
    {
        $this->studio = $studio;
        $this->moreByStudioCount = $this->studio->mediaStudios()->count();
    }

    /**
     * Loads the more by studio section.
     *
     * @return void
     */
    public function loadMoreByStudio(): void
    {
        $this->moreByStudio = $this->studio->getManga(Studio::MAXIMUM_RELATIONSHIPS_LIMIT)->items() ?? [];
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.manga-more-by-studio-section');
    }
}
