<?php

namespace App\Http\Livewire\Misc;

use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class Creators extends Component
{
    /**
     * Get the list of users.
     *
     * @return User[]|Collection
     */
    public function getUsersProperty(): array|Collection
    {
        return User::whereIn('id', [1, 2])->get();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.misc.creators');
    }
}
