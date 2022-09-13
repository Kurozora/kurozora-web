<?php

namespace App\Http\Livewire\Profile\Badges;

use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;

class Index extends Component
{
    /**
     * The object containing the user data.
     *
     * @var User $badges
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
     * The user's badges.
     *
     * @return LengthAwarePaginator
     */
    public function getBadgesProperty(): LengthAwarePaginator
    {
        return $this->user->badges()->paginate(25);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.badges.index');
    }
}
