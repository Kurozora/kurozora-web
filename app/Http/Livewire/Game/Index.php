<?php

namespace App\Http\Livewire\Game;

use App\Traits\Livewire\WithGameSearch;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
{
    use WithGameSearch;

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount(): void
    {
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.game.index');
    }
}
