<?php

namespace App\Livewire\Components;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class SearchHintButton extends Component
{
    /**
     * Whether to show the help modal.
     *
     * @var bool $showHelp
     */
    public bool $showHelp = false;

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.search-hint-button');
    }
}
