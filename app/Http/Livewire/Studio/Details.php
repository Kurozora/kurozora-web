<?php

namespace App\Http\Livewire\Studio;

use App\Events\StudioViewed;
use App\Models\Studio;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;

class Details extends Component
{
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
        // Call the StudioViewed event
        StudioViewed::dispatch($studio);

        $this->studio = $studio;
    }

    /**
     * The studio's animes.
     *
     * @return LengthAwarePaginator
     */
    public function getAnimesProperty(): LengthAwarePaginator
    {
        return $this->studio->anime()->paginate(Studio::MAXIMUM_RELATIONSHIPS_LIMIT);
    }

    /**
     * The studio's mangas.
     *
     * @return LengthAwarePaginator
     */
    public function getMangasProperty(): LengthAwarePaginator
    {
        return $this->studio->manga()->paginate(Studio::MAXIMUM_RELATIONSHIPS_LIMIT);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.studio.details');
    }
}
