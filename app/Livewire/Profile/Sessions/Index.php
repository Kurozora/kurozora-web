<?php

namespace App\Livewire\Profile\Sessions;

use App\Traits\InteractsWithSessions;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Index extends Component
{
    use InteractsWithSessions;

    /**
     * Whether the sign-out confirmation modal is shown.
     *
     * @var bool $confirmingSignOut
     */
    public bool $confirmingSignOut = false;

    /**
     * Whether the pending confirmation signs out every other session.
     *
     * @var bool $signingOutAll
     */
    public bool $signingOutAll = false;

    /**
     * The session keys pending sign out.
     *
     * @var array $pendingKeys
     */
    public array $pendingKeys = [];

    /**
     * The user's current password.
     *
     * @var string $password
     */
    public string $password = '';

    /**
     * Confirms signing out the given sessions.
     *
     * @param array $keys
     *
     * @return void
     */
    public function confirmSignOutSelected(array $keys): void
    {
        if (empty($keys)) {
            return;
        }

        $this->pendingKeys = $keys;
        $this->signingOutAll = false;
        $this->password = '';
        $this->confirmingSignOut = true;
    }

    /**
     * Confirms signing out every other session.
     *
     * @return void
     */
    public function confirmSignOutAll(): void
    {
        $this->pendingKeys = [];
        $this->signingOutAll = true;
        $this->password = '';
        $this->confirmingSignOut = true;
    }

    /**
     * Signs out the pending sessions after confirming the password.
     *
     * @return void
     * @throws ValidationException
     */
    public function signOut(): void
    {
        $this->resetErrorBag();

        if ($this->signingOutAll) {
            $this->signOutAllOtherSessions($this->password);
        } else {
            $this->signOutSessions($this->pendingKeys, $this->password);
        }

        $this->confirmingSignOut = false;
        $this->signingOutAll = false;
        $this->pendingKeys = [];
        $this->password = '';

        $this->dispatch('signedOut');
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        $currentSession = $this->currentSessionView();
        $otherSessions = $this->otherSessionViews();

        $coordinates = collect([$currentSession])
            ->concat($otherSessions)
            ->filter()
            ->filter(fn ($session) => $session->latitude !== null && $session->longitude !== null && !($session->latitude == 0 && $session->longitude == 0))
            ->map(fn ($session) => [
                'latitude' => (float) $session->latitude,
                'longitude' => (float) $session->longitude,
                'title' => $session->full_platform,
                'subtitle' => $session->full_location,
            ])
            ->values();

        return view('livewire.profile.sessions.index', [
            'currentSession' => $currentSession,
            'otherSessions' => $otherSessions,
            'mapToken' => config('services.apple.maps.token'),
            'coordinates' => $coordinates,
        ]);
    }
}
