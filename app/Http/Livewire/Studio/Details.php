<?php

namespace App\Http\Livewire\Studio;

use App\Models\Studio;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
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
    public function mount(Studio $studio)
    {
        $this->studio = $studio;
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.studio.details', [
            'studioAnime' => $this->studio->getAnime(Studio::MAXIMUM_RELATIONSHIPS_LIMIT)
        ])
            ->layout('layouts.base');
    }
}
