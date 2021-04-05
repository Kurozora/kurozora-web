<?php

namespace App\Http\Livewire;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class NavigationDropdown extends Component
{
    /**
     * The component's listeners.
     *
     * @var array
     */
    protected $listeners = [
        'refresh-navigation-dropdown' => '$refresh',
    ];

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render()
    {
        return view('livewire.navigation-dropdown');
    }
}
