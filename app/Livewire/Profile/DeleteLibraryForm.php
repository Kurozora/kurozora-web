<?php

namespace App\Livewire\Profile;

use App\Contracts\DeletesLibraries;
use App\Enums\UserLibraryKind;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class DeleteLibraryForm extends Component
{
    /**
     * The component's state.
     *
     * @var array
     */
    public array $state = [];

    /**
     * Indicates if library deletion is being confirmed.
     *
     * @var bool
     */
    public bool $confirmingLibraryDeletion = false;

    /**
     * The user's current password.
     *
     * @var string
     */
    public string $password = '';

    /**
     * Confirm that the user would like to delete their library.
     *
     * @return void
     * @throws ValidationException
     */
    public function confirmLibraryDeletion(): void
    {
        Validator::make($this->state, [
            'library' => ['required', 'integer', 'in:' . implode(',', UserLibraryKind::getValues())],
        ])->validateWithBag('deleteUserLibrary');

        $this->resetErrorBag();

        $this->password = '';

        $this->dispatch('confirming-delete-library');

        $this->confirmingLibraryDeletion = true;
    }

    /**
     * Delete the current library.
     *
     * @param DeletesLibraries $deleter
     *
     * @throws ValidationException
     */
    public function deleteLibrary(DeletesLibraries $deleter): void
    {
        $this->resetErrorBag();

        if (!Hash::check($this->password, auth()->user()->password)) {
            throw ValidationException::withMessages([
                'password' => [__('This password does not match our records.')],
            ]);
        }

        $this->password = '';

        $this->confirmingLibraryDeletion = false;

        $deleter->delete(auth()->user(), $this->state);

        $this->dispatch('deleted');
    }

    /**
     * Returns an instance of UserLibraryKind.
     *
     * @return null|UserLibraryKind
     */
    public function getLibraryProperty(): ?UserLibraryKind
    {
        if (!isset($this->state['library'])) {
            return null;
        }
        return UserLibraryKind::fromValue((int) $this->state['library']);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.delete-library-form');
    }
}
