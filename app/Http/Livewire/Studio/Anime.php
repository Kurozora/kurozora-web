<?php

namespace App\Http\Livewire\Studio;

use App\Models\Studio;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class Anime extends Component
{
    use WithPagination;

    /**
     * The object containing the studio data.
     *
     * @var Studio $studio
     */
    public Studio $studio;

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
    }

    /**
     * The studio's animes.
     *
     * @return LengthAwarePaginator
     */
    public function getAnimesProperty(): LengthAwarePaginator
    {
        return $this->studio->anime()->paginate(25);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.studio.anime');
    }
}
