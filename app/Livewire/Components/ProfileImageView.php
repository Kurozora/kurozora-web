<?php

namespace App\Livewire\Components;

use App\Enums\UserActivityStatus;
use App\Models\User;
use App\Services\Presence\PresenceTracker;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ProfileImageView extends Component
{
    /**
     * The object containing the user data.
     *
     * @var User $user
     */
    public User $user;

    /**
     * Whether the image is shown on a user's profile page.
     *
     * @var bool $onProfile
     */
    public bool $onProfile;

    /**
     * Register listeners for the component.
     *
     * @return array
     */
    public function getListeners(): array
    {
        $listeners = ['refresh-profile-image' => '$refresh'];

        if ($this->onProfile) {
            $listeners['echo:user-status.' . $this->user->id . ',.user.status.changed'] = 'onStatusChanged';
        }

        return $listeners;
    }

    /**
     * Refreshes the cached activity status when a live transition arrives.
     *
     * @return void
     */
    public function onStatusChanged(): void
    {
        unset($this->activityStatus);
    }

    /**
     * Returns the user's activity status derived solely from the live presence tracker.
     *
     * @return UserActivityStatus
     */
    #[Computed]
    public function activityStatus(): UserActivityStatus
    {
        $tracker = app(PresenceTracker::class);
        $userId = (int) $this->user->id;

        if ($tracker->isUserGloballyOnline($userId)) {
            return UserActivityStatus::Online();
        }

        if ($tracker->isSeenRecently($userId)) {
            return UserActivityStatus::SeenRecently();
        }

        return UserActivityStatus::Offline();
    }

    /**
     * Prepare the component.
     *
     * @param User $user
     * @param bool $onProfile
     * @return void
     */
    public function mount(User $user, bool $onProfile = false): void
    {
        $this->onProfile = $onProfile;
        $this->user = $user;
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.profile-image-view');
    }
}
