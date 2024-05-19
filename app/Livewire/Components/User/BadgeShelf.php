<?php

namespace App\Livewire\Components\User;

use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class BadgeShelf extends Component
{

    /**
     * The object containing the user data.
     *
     * @var User $user
     */
    public User $user;

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.user.badge-shelf');
    }
}
