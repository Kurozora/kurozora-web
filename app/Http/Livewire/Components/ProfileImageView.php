<?php

namespace App\Http\Livewire\Components;

use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
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
     * The component's listeners.
     *
     * @var array
     */
    protected $listeners = [
        'refresh-profile-image' => '$refresh',
    ];

    /**
     * Prepare the component.
     *
     * @param User $user
     * @param bool $onProfile
     * @return void
     */
    public function mount(User $user, bool $onProfile = false): void
    {
        $this->user = $user;
        $this->onProfile = $onProfile;
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
