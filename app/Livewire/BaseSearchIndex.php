<?php

namespace App\Livewire;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

abstract class BaseSearchIndex extends Component
{
    /**
     * The view to render.
     *
     * @var string $view
     */
    protected string $view = '';

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
        return view($this->view);
    }
}
