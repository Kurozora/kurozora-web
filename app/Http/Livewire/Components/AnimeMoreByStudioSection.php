<?php

namespace App\Http\Livewire\Components;

use App\Models\Studio;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class AnimeMoreByStudioSection extends Component
{
    /**
     * The object containing the studio data.
     *
     * @var Studio|null
     */
    public ?Studio $studio;

    /**
     * The array containing the more by studio data.
     *
     * @var array $moreByStudio
     */
    public array $moreByStudio = [];

    /**
     * Indicates whether to load the more by studio section.
     *
     * @var int $moreByStudioCount
     */
    public int $moreByStudioCount;

    /**
     * Prepare the component.
     *
     * @param ?Studio $studio
     *
     * @return void
     */
    public function mount(?Studio $studio)
    {
        $this->studio = $studio;
        $this->moreByStudioCount = $this->studio?->anime_studio()->count() ?? 0;
    }

    /**
     * Loads the more by studio section.
     *
     * @return void
     */
    public function loadMoreByStudio()
    {
        $this->moreByStudio = $this->studio?->getAnime(Studio::MAXIMUM_RELATIONSHIPS_LIMIT)->items() ?? [];
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.anime-more-by-studio-section');
    }
}