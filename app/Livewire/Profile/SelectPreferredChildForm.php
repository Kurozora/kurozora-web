<?php

namespace App\Livewire\Profile;

use App\Models\User;
use App\Rules\ValidateEmail;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

class SelectPreferredChildForm extends Component
{
    /**
     * The user instance.
     *
     * @var User
     */
    public User $user;

    /**
     * The email to end an invitation to.
     *
     * @var string
     */
    #[Validate('required')]
    #[Validate([
        new ValidateEmail(['must-be-available' => true])
    ])]
    public string $email = '';

    /**
     * Indicates if user deletion is being confirmed.
     *
     * @var bool
     */
    public bool $confirmingChildDeletion = false;

    /**
     * The user's current password.
     *
     * @var string
     */
    public string $password = '';

    /**
     * The child instance to delete.
     *
     * @var null|User
     */
    public ?User $childToDelete = null;

    /**
     * The user's children.
     *
     * @var User[]
     */
    public Collection|array $children = [];

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
        $this->children = $user->children;
    }

    public function inviteChild(): void
    {
        $this->validateOnly('email');

        // Logic for inviting a child (to be implemented)
        // e.g., sending an email invitation or creating a pending child account
        session()->flash('message', __('Invitation sent successfully.'));
        $this->reset('email');
    }

    public function confirmChildDeletion($childId): void
    {
        if ($this->children->firstWhere('id', '=', $childId) === null) {
            return;
        }

        $this->resetErrorBag();

        $this->password = '';
        $this->childToDelete = $this->children->firstWhere('id', '=', $childId);

        $this->dispatch('confirming-delete-child');

        $this->confirmingChildDeletion = true;
    }

    public function unlinkChild(): void
    {
        $this->resetErrorBag();

        if (!Hash::check($this->password, $this->user->password)) {
            throw ValidationException::withMessages([
                'password' => [__('This password does not match our records.')],
            ]);
        }

        // Remove parent_id from the child's account
        $this->childToDelete?->update([
            'parent_id' => null
        ]);

        $this->confirmingChildDeletion = false;

        session()->flash('message', __('Child account unlinked successfully.'));
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.select-preferred-child-form');
    }
}
