<?php

namespace App\Http\Livewire\Misc;

use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Creators extends Component
{
    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.misc.creators', [
            'users' => User::whereIn('id', [1, 2])->get()
        ])
            ->layout('layouts.base');
    }
}