<?php

namespace App\Livewire\Profile;

use App\Contracts\DeletesUsers;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class DeleteUserForm extends Component
{
    /**
     * Indicates if user deletion is being confirmed.
     *
     * @var bool
     */
    public bool $confirmingUserDeletion = false;

    /**
     * The user's current password.
     *
     * @var string
     */
    public string $password = '';

    /**
     * Confirm that the user would like to delete their account.
     *
     * @return void
     */
    public function confirmUserDeletion(): void
    {
        $this->resetErrorBag();

        $this->password = '';

        $this->dispatch('confirming-delete-user');

        $this->confirmingUserDeletion = true;
    }

    /**
     * Delete the current user.
     *
     * @param DeletesUsers $deleter
     *
     * @return Application|RedirectResponse|Redirector
     * @throws ValidationException
     */
    public function deleteUser(DeletesUsers $deleter): Application|RedirectResponse|Redirector
    {
        $this->resetErrorBag();

        if (!Hash::check($this->password, auth()->user()->password)) {
            throw ValidationException::withMessages([
                'password' => [__('This password does not match our records.')],
            ]);
        }

        $deleter->delete(auth()->user()->fresh());

        auth()->logout();

        return redirect('/');
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.delete-user-form');
    }
}
