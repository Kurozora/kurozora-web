<?php

namespace App\Http\Livewire\Profile;

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
     * @var ?User $user
     */
    public ?User $user;

    /**
     * The page's data.
     *
     * @var array
     */
    public array $page = [
        'title' => '',
        'image' => '',
        'type' => 'profile',
    ];

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

        $this->page['title'] = $user->username . ' on Kurozora';
        $this->page['image'] = $user->profile_image;
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Factory|View|Application
    {
        return view('livewire.profile.details')
            ->layout('layouts.base');
    }
}
