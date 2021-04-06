<?php

namespace App\Http\Livewire\Theme;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CreateThemeForm extends Component
{
    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): View|Factory|Application
    {
        return view('livewire.theme.create-theme-form');
    }
}
