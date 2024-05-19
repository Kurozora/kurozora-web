<?php

namespace App\Livewire\KnowledgeBase;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class InAppPurchases extends Component
{
    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.knowledge-base.in-app-purchases');
    }
}
