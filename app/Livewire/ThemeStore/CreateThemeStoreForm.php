<?php

namespace App\Livewire\ThemeStore;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CreateThemeStoreForm extends Component
{
    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.theme-store.create-theme-store-form');
    }
}
