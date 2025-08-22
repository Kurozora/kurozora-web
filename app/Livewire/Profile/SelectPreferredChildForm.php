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
