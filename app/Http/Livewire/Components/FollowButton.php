<?php

namespace App\Http\Livewire\Components;

use App\Models\User;
use App\Notifications\NewFollower;
use Auth;
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
     * @return Application|RedirectResponse|Redirector|null
     */
    public function toggleFollow(): Application|RedirectResponse|Redirector|null
    {
        $authUser = Auth::user();

        // Require user to authenticate if necessary.
        if (empty($authUser)) {
            return redirect(route('sign-in'));
        }

        // Determine if the user is already followed
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
