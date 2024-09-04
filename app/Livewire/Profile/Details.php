<?php

namespace App\Livewire\Profile;

use App\Events\ModelViewed;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Details extends Component
{
    /**
     * The object containing the user data.
     *
     * @var User $user
     */
    public User $user;

    /**
     * Whether to show the popup to the user.
     *
     * @var bool $showPopup
     */
    public bool $showPopup = false;

    /**
     * The selected popup type.
     *
     * @var string $selectedPopupType
     */
    public string $selectedPopupType = '';

    /**
     * The data used to populate the popup.
     *
     * @var array|string[]
     */
    public array $popupData = [
        'title' => '',
        'message' => '',
    ];

    /**
     * The array off counts.
     *
     * @var array|int[]
     */
    public array $counts = [
        'achievements_count' => 0,
        'followers_count' => 0,
        'following_count' => 0,
        'media_ratings_count' => 0,
    ];

    /**
     * List of acceptable popup types.
     *
     * @var array|string[]
     */
    private array $popupTypes = [
        'edit',
        'achievements',
        'following',
        'followers',
    ];

    /**
     * The component's listeners.
     *
     * @var array
     */
    protected $listeners = [
        'followers-badge-refresh' => 'followersCountUpdated'
    ];

    /**
     * Prepare the component.
     *
     * @param User $user
     *
     * @return void
     */
    public function mount(User $user): void
    {
        // Call the ModelViewed event
        ModelViewed::dispatch($user, request()->ip());

        $this->user = $user->load(['media'])
            ->loadCount([
                'badges',
                'followers',
                'followedModels as following_count',
                'mediaRatings'
            ]);

        $this->counts = [
            'achievements_count' => $user->badges_count,
            'followers_count' => $user->followers_count,
            'following_count' => $user->following_count,
            'media_ratings_count' => $user->media_ratings_count,
        ];
    }

    /**
     * Toggles the popup if the given type is accepted.
     *
     * @param string|null $type
     * @return void
     */
    public function togglePopupFor(?string $type): void
    {
        if (!is_string($type) && !in_array($type, $this->popupTypes)) {
            return;
        }

        $this->selectedPopupType = $type;
        $this->showPopup = true;
    }

    /**
     * Whether the user is followed by the auth user.
     *
     * @return bool
     */
    public function getIsFollowedProperty(): bool
    {
        if ($authUser = auth()->user()) {
            return $this->user
                ->followers()
                ->where('user_id', '=', $authUser->id)
                ->exists();
        }

        return false;
    }

    /**
     * Updates the user's followers count.
     *
     * @param int $newCount
     * @param $userID
     * @return void
     */
    public function followersCountUpdated(int $newCount, $userID): void
    {
        if ($this->user->id == $userID) {
            $this->counts['followers_count'] += $newCount;
        }
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.details');
    }
}
