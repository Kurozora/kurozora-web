<?php

namespace App\Livewire\Components;

use App\Models\User;
use App\Notifications\NewFollower;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Livewire\Component;

class FollowButton extends Component
{
    /**
     * The object containing the user data.
     *
     * @var User $user
     */
    public User $user;

    /**
     * Whether the user is being followed by the auth user.
     *
     * @var bool $isFollowed
     */
    public bool $isFollowed;

    /**
     * Prepare the component.
     *
     * @param User $user
     * @param bool $isFollowed
     * @return void
     */
    public function mount(User $user, bool $isFollowed): void
    {
        $this->user = $user;
        $this->isFollowed = $isFollowed;
    }

    /**
     * Toggles the follow status of the user.
     *
     * @return Application|RedirectResponse|Redirector|null
     */
    public function toggleFollow(): Application|RedirectResponse|Redirector|null
    {
        $authUser = auth()->user();
        $followersCount = 0;

        // Require user to authenticate if necessary.
        if (empty($authUser)) {
            return redirect(route('sign-in'));
        }

        if ($this->isFollowed) {
            // Delete follow
            $this->user->followers()->detach($authUser);
            $followersCount--;
        } else {
            // Follow the user
            $this->user->followers()->attach($authUser);
            $followersCount++;

            // Send notification
            $this->user->notify(new NewFollower($authUser));
        }

        $this->isFollowed = !$this->isFollowed;

        // Notify relevant components to increment or decrement count by 1
        $this->dispatch('followers-badge-refresh', followersCount: $followersCount, userID: $this->user->id);

        return null;
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.follow-button');
    }
}
