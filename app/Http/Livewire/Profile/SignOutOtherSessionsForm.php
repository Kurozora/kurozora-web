<?php

namespace App\Http\Livewire\Profile;

use App\Models\Session;
use Auth;
use Browser;
use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class SignOutOtherSessionsForm extends Component
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
     * Confirm that the user would like to sign out from other browser sessions.
     *
     * @return void
     */
    public function confirmSignOut()
    {
        $this->password = '';

        $this->dispatchBrowserEvent('confirming-sign-out-other-browser-sessions');

        $this->confirmingSignOut = true;
    }

    /**
     * Sign out from other browser sessions.
     *
     * @return void
     * @throws ValidationException
     * @throws AuthenticationException
     */
    public function signOutOtherBrowserSessions()
    {
        $this->resetErrorBag();

        if (!Hash::check($this->password, Auth::user()->password)) {
            throw ValidationException::withMessages([
                'password' => [__('This password does not match our records.')],
            ]);
        }

        Auth::logoutOtherDevices($this->password);

        $this->deleteOtherSessionRecords();

        $this->confirmingSignOut = false;

        $this->emit('signedOut');
    }

    /**
     * Delete the other browser session records from storage.
     *
     * @return void
     */
    protected function deleteOtherSessionRecords()
    {
       Session::where('user_id', Auth::user()->id)
           ->where('session_id', '!=', request()->session()->getId())
           ->delete();
    }

    /**
     * Get the current sessions.
     *
     * @return Collection
     */
    public function getSessionsProperty(): Collection
    {
        $otherSessions = Session::where('user_id', Auth::user()->id)
            ->where('session_id', '!=', request()->session()->getId())
            ->orderBy('last_activity_at', 'desc')
            ->get();

        $currentSession = Session::where('user_id', Auth::user()->id)
            ->where('session_id', request()->session()->getId())
            ->first();

        return collect(
            $otherSessions->prepend($currentSession)
        )
            ->where('session_id', '!=', null)
            ->map(function (Session $session) {
            return (object) [
                'browser'           => Browser::detect(),
                'platform'          => $session->platform,
                'platform_version'  => $session->platform_version,
                'device_model'      => $session->device_model,
                'ip_address'        => $session->ip_address,
                'is_current_device' => $session->session_id === request()->session()->getId(),
                'last_activity_at'  => Carbon::parse($session->last_activity_at)->diffForHumans(),
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
        return view('livewire.profile.sign-out-other-sessions-form');
    }
}
