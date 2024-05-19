<?php

namespace App\Livewire\Misc;

use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class Team extends Component
{
    /**
     * Get the list of users.
     *
     * @return User[]|Collection
     */
    public function getUsersProperty(): array|Collection
    {
        $users = User::whereIn('id', [
            1, 2, 380, 461, 668, 1110
        ])
            ->with(['media'])
            ->get();

        return [
            $users[1],
            $users[0],
            $users[4],
            $users[3],
            $users[2],
            $users[5],
        ];
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.misc.team');
    }
}
