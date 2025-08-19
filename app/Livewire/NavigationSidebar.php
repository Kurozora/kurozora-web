<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class NavigationSidebar extends Component
{
    /**
     * The object containing the user data.
     *
     * @var User|null $user
     */
    public ?User $user;

    /**
     * Prepare the component.
     *
     * @param ?User $user
     *
     * @return void
     */
    public function mount($user): void
    {
        $this->user = $user?->loadMissing(['media']);
    }

    /**
     * The component's listeners.
     *
     * @var array
     */
    protected $listeners = [
        'refresh-navigation-dropdown' => '$refresh',
    ];

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.navigation-sidebar');
    }
}
