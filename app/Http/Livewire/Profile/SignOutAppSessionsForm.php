<?php

namespace App\Http\Livewire\Profile;

use App\Models\PersonalAccessToken;
use Auth;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class SignOutAppSessionsForm extends Component
{
    /**
     * Indicates if sign out is being confirmed.
     *
     * @var bool
     */
    public bool $confirmingSignOut = false;

    /**
     * The user's current password.
     *
     * @var string
     */
    public string $password = '';

    /**
     * Confirm that the user would like to sign out from app sessions.
     *
     * @return void
     */
    public function confirmSignOut()
    {
        $this->password = '';

        $this->dispatchBrowserEvent('confirming-sign-out-app-sessions');

        $this->confirmingSignOut = true;
    }

    /**
     * Sign out from app sessions.
     *
     * @return void
     * @throws ValidationException
     */
    public function signOutAppSessions()
    {
        $this->resetErrorBag();

        if (!Hash::check($this->password, Auth::user()->password)) {
            throw ValidationException::withMessages([
                'password' => [__('This password does not match our records.')],
            ]);
        }

        Auth::user()->tokens()->delete();

        $this->confirmingSignOut = false;

        $this->emit('signedOut');
    }

    /**
     * Get the current tokens.
     *
     * @return Collection
     */
    public function getTokensProperty(): Collection
    {
        $currentTokens = Auth::user()->tokens;

        return $currentTokens->map(function (PersonalAccessToken $personalAccessToken) {
            return (object) [
                'name'          => $personalAccessToken->name,
                'last_activity' => $personalAccessToken->last_used_at->diffForHumans(),
            ];
        });
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.sign-out-app-sessions-form');
    }
}