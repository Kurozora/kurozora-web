<?php

namespace App\Livewire\Profile;

use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class UnlinkUserForm extends Component
{
    /**
     * The object containing the user data.
     *
     * @var User
     */
    public User $user;

    /**
     * Indicates if user disconnecting is being confirmed.
     *
     * @var bool
     */
    public bool $confirmingUserDisconnect = false;

    /**
     * The user's current password.
     *
     * @var string
     */
    public string $password = '';

    /**
     * Prepare the component.
     *
     * @param User $user
     *
     * @return void
     */
    public function mount(User $user): void
    {
        $this->user = $user;
    }

    /**
     * Confirm that the user would like to disconnect their account.
     *
     * @return void
     */
    public function confirmUserDisconnect(): void
    {
        $this->resetErrorBag();

        $this->password = '';

        $this->dispatch('confirming-disconnect-user');

        $this->confirmingUserDisconnect = true;
    }

    /**
     * Disconnect the child user.
     *
     * @return void
     * @throws ValidationException
     */
    public function disconnectUser(): void
    {
        $this->resetErrorBag();

        if (!Hash::check($this->password, auth()->user()->password)) {
            throw ValidationException::withMessages([
                'password' => [__('This password does not match our records.')],
            ]);
        }

        // Remove parent_id from the child's account
        $this->user->update([
            'parent_id' => null
        ]);

        session()->flash('message', __('Child account disconnected successfully.'));

        $this->redirectRoute('profile.settings');
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.unlink-user-form');
    }
}

