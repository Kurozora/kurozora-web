<?php

namespace App\Http\Livewire\Manga;

use App\Models\Manga;
use App\Models\MediaStaff;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class Staff extends Component
{
    use WithPagination;

    /**
     * The object containing the manga data.
     *
     * @var Manga $manga
     */
    public Manga $manga;

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
    }

    /**
     * Get the list of media staff.
     *
     * @return MediaStaff[]|LengthAwarePaginator
     */
    public function getMediaStaffProperty(): array|LengthAwarePaginator
    {
        return $this->manga->mediaStaff()->paginate(25);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.manga.staff');
    }
}
