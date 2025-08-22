<?php

namespace App\Livewire\Profile;

use App\Models\User;
use App\Notifications\InviteToFamilyEmail;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Collection;
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
    #[Validate('email:filter,dns')]
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

    /**
     * Invite an account to join the family.
     *
     * @return void
     */
    public function inviteAccount(): void
    {
        $this->validateOnly('email');

        $user = User::where('email', '=', $this->email)
            ->where('id', '!=', auth()->id())
            ->first();

        if ($user) {
            $user->notify(new InviteToFamilyEmail());
        }

        $this->dispatch('invitation-sent');
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
