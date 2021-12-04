<?php

namespace App\Http\Livewire\Studio;

use App\Models\Studio;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
{
    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount() {}

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.studio.index', [
            'studios' => Studio::orderBy('name')->paginate(100),
        ])
            ->layout('layouts.base');
    }
}
