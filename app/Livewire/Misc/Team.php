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
     * Get the list of current staff.
     *
     * @return User[]|Collection
     */
    public function getStaffProperty(): array|Collection
    {
        $users = User::whereIn('id', [
            2, 380, 461, 668, 1110, 2116
        ])
            ->with(['media'])
            ->get();

        return [
            $users[0],
            $users[3],
            $users[2],
            $users[1],
            $users[4],
            $users[5],
        ];
    }

    /**
     * Get the list of ex-staff.
     *
     * @return User[]|Collection
     */
    public function getExStaffProperty(): array|Collection
    {
        $users = User::whereIn('id', [
            1
        ])
            ->with(['media'])
            ->get();

        return [
            $users[0],
        ];
    }

    /**
     * Get the total number of users.
     *
     * @return string
     */
    public function getUserCountProperty(): string
    {
        return number_shorten(User::count(), 1, true);
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
