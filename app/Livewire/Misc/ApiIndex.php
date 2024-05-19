<?php

namespace App\Livewire\Misc;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ApiIndex extends Component
{
    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.misc.api-index')
            ->layout('components.layouts.empty');
    }
}
