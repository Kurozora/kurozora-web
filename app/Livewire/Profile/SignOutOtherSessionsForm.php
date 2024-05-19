<?php

namespace App\Livewire\Profile;

use App\Models\Session;
use Browser;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
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
     * Determines whether to load the section.
     *
     * @var bool $readyToLoad
     */
    public $readyToLoad = false;

    /**
     * Sets the property to load the section.
     *
     * @return void
     */
    public function loadSection(): void
    {
        $this->readyToLoad = true;
    }

    /**
     * Confirm that the user would like to sign out from other browser sessions.
     *
     * @return void
     */
    public function confirmSignOut(): void
    {
        $this->password = '';

        $this->dispatch('confirming-sign-out-other-browser-sessions');

        $this->confirmingSignOut = true;
    }

    /**
     * Sign out from other browser sessions.
     *
     * @return void
     * @throws ValidationException
     * @throws AuthenticationException
     */
    public function signOutOtherBrowserSessions(): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        $this->resetErrorBag();

        if (!Hash::check($this->password, auth()->user()->password)) {
            throw ValidationException::withMessages([
                'password' => [__('This password does not match our records.')],
            ]);
        }

        auth()->logoutOtherDevices($this->password);

        $this->deleteOtherSessionRecords();

        request()->session()->put([
            'password_hash_' . auth()->getDefaultDriver() => auth()->user()->getAuthPassword(),
        ]);

        $this->confirmingSignOut = false;

        $this->dispatch('signedOutBrowser');
    }

    /**
     * Delete the other browser session records from storage.
     *
     * @return void
     */
    protected function deleteOtherSessionRecords(): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        Session::where('user_id', auth()->user()->getAuthIdentifier())
            ->where('id', '!=', request()->session()->getId())
            ->delete();
    }

    /**
     * Get the current sessions.
     *
     * @return Collection
     */
    public function getSessionsProperty(): Collection
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        if (config('session.driver') !== 'database') {
            return collect();
        }

        $otherSessions = Session::with(['session_attribute'])
            ->where([
                ['id', '!=', request()->session()->getId()],
                ['user_id', '=', auth()->user()->getAuthIdentifier()],
            ])
            ->orderBy('last_activity', 'desc')
            ->get();

        $currentSession = Session::with(['session_attribute'])
            ->where([
                ['id', '=', request()->session()->getId()],
                ['user_id', '=', auth()->user()->getAuthIdentifier()],
            ])
            ->first();

        return $otherSessions->prepend($currentSession)
            ->where('id', '!=', null)
            ->map(function (Session $session) {
                $sessionAttribute = $session->session_attribute;

                return (object) [
                    'browser'           => Browser::detect(),
                    'platform'          => $sessionAttribute->platform ?? 'Unknown',
                    'platform_version'  => $sessionAttribute->platform_version ?? 'Unknown',
                    'device_model'      => $sessionAttribute->device_model ?? 'Unknown',
                    'ip_address'        => $sessionAttribute->ip_address ?? 'Unknown',
                    'is_current_device' => $session->id === request()->session()->getId(),
                    'last_activity'     => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
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
