<?php

namespace App\View\Components\User;

use App\Enums\MediaCollection;
use App\Enums\UserActivityStatus;
use App\Models\User;
use App\Services\Presence\PresenceTracker;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProfileImage extends Component
{
    /**
     * The user whose profile image is shown.
     *
     * @var User $user
     */
    public User $user;

    /**
     * Whether the image is shown on the user's profile page.
     *
     * @var bool $onProfile
     */
    public bool $onProfile;

    /**
     * The user's profile image.
     *
     * @var Media|null $profileImage
     */
    public ?Media $profileImage;

    /**
     * The user's live activity status on the profile page.
     *
     * @var UserActivityStatus|null $activityStatus
     */
    public ?UserActivityStatus $activityStatus;

    /**
     * The activity status labels keyed by status value.
     *
     * @var array $statusLabels
     */
    public array $statusLabels;

    /**
     * The URL that re-renders the image.
     *
     * @var string $refreshUrl
     */
    public string $refreshUrl;

    /**
     * Create a new component instance.
     *
     * @param User $user
     * @param bool $onProfile
     */
    public function __construct(User $user, bool $onProfile = false)
    {
        $this->user = $user;
        $this->onProfile = $onProfile;
        $this->profileImage = $user->getFirstMedia(MediaCollection::Profile);
        $this->activityStatus = $onProfile ? $this->resolveActivityStatus() : null;
        $this->statusLabels = [
            UserActivityStatus::Online => UserActivityStatus::getDescription(UserActivityStatus::Online),
            UserActivityStatus::SeenRecently => UserActivityStatus::getDescription(UserActivityStatus::SeenRecently),
            UserActivityStatus::Offline => UserActivityStatus::getDescription(UserActivityStatus::Offline),
        ];
        $this->refreshUrl = route('profile.section', [$user, 'profile-image'], false);
    }

    /**
     * Get the view that represents the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.user.profile-image');
    }

    /**
     * Resolve the user's activity status from the live presence tracker.
     *
     * @return UserActivityStatus
     */
    protected function resolveActivityStatus(): UserActivityStatus
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
}
