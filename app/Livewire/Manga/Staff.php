<?php

namespace App\Livewire\Manga;

use App\Models\Manga;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
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
        $this->manga = $manga->load(['media', 'translations']);
    }

    /**
     * Sets the property to load the page.
     *
     * @return void
     */
    public function loadPage(): void
    {
        $this->readyToLoad = true;
    }

    /**
     * Get the list of media staff.
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getMediaStaffProperty(): Collection|LengthAwarePaginator
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return $this->manga->mediaStaff()
            ->with([
                'person' => function ($query) {
                    $query->with(['media']);
                },
                'staff_role'
            ])
            ->paginate(25);
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
