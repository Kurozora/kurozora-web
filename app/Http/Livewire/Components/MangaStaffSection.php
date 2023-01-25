<?php

namespace App\Http\Livewire\Components;

use App\Models\Manga;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class MangaStaffSection extends Component
{
    /**
     * The object containing the manga data.
     *
     * @var Manga $manga
     */
    public Manga $manga;

    /**
     * The number of staff the manga has.
     *
     * @var int $staffCount
     */
    public int $staffCount;

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
        $this->staffCount = $manga->mediaStaff()->count();
    }

    /**
     * Loads the media staff section.
     *
     * @return array
     */
    public function getMediaStaffProperty(): array
    {
        return $this->manga->getMediaStaff(Manga::MAXIMUM_RELATIONSHIPS_LIMIT)->items() ?? [];
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.manga-staff-section');
    }
}
