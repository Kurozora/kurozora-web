<?php

namespace App\Livewire\Profile;

use App\Contracts\UpdatesUserPasswords;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class UpdatePasswordForm extends Component
{
    /**
     * The component's state.
     *
     * @var array
     */
    public array $state = [
        'current_password'      => '',
        'password'              => '',
        'password_confirmation' => '',
    ];

    /**
     * Update the user's password.
     *
     * @param UpdatesUserPasswords $updater
     * @return void
     */
    public function updatePassword(UpdatesUserPasswords $updater): void
    {
        $this->resetErrorBag();

        $updater->update(auth()->user(), $this->state);

        $this->state = [
            'current_password' => '',
            'password' => '',
            'password_confirmation' => '',
        ];

        $this->dispatch('saved');
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.update-password-form');
    }
}

