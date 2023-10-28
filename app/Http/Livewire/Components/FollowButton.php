<?php

namespace App\Http\Livewire\Components;

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
     * Whether the auth user is following the user.
     *
     * @var bool $isFollowing
     */
    public bool $isFollowing;

    /**
     * Prepare the component.
     *
     * @param User $user
     * @param bool $isFollowing
     * @return void
     */
    public function mount(User $user, bool $isFollowing): void
    {
        $this->user = $user;
        $this->isFollowing = $isFollowing;
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

        if ($this->isFollowing) {
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

        $this->isFollowing = !$this->isFollowing;
        $this->emit('followers-badge-refresh', $followersCount, $this->user->id);

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
