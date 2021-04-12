<?php

namespace App\Http\Livewire\Email;

use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Verification extends Component
{
    /**
     * The component's state.
     *
     * @var array $state
     */
    public array $state = [];

    /**
     * The email verification ID.
     *
     * @var string $verificationID
     */
    private string $verificationID = '';

    /**
     * The verified email address.
     *
     * @var string $email
     */
    public string $email = '';

    /**
     * Prepare the component.
     *
     * @param string $verificationID
     *
     * @return void
     */
    public function mount(string $verificationID)
    {
        $this->verificationID = $verificationID;

        // Try to find a user with this confirmation ID
        // If no user is found then throw a ModelNotFoundException.
        /** @var User $user */
        $user = User::where('email_confirmation_id', $this->verificationID)->firstOrFail();

        // Confirm their email and show the page
        $user->email_confirmation_id = null;
        $user->save();

        $this->email = $user->email;
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.email.verification');
    }
}
