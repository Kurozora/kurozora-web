<?php

namespace App\Http\Livewire\Components;

use App\Models\User;
use App\Notifications\NewFollower;
use Auth;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
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
     * Prepare the component.
     *
     * @param User $user
     *
     * @return void
     */
    public function mount(User $user)
    {
        $this->user = $user;
    }

    /**
     * Toggles the follow status of the user.
     *
     * @return void
     */
    public function toggleFollow()
    {
        $authUser = Auth::user();

        $isAlreadyFollowing = $this->user->followers()->where('user_id', $authUser->id)->exists();

        if ($isAlreadyFollowing) {
            // Delete follow
            $this->user->followers()->detach($authUser);
        } else {
            // Follow the user
            $this->user->followers()->attach($authUser);

            // Send notification
            $this->user->notify(new NewFollower($authUser));
        }

        $this->emit('followers-badge-refresh');
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
