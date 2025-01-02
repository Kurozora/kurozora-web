<?php

namespace App\Livewire\Profile;

use App\Contracts\UpdatesUserAccountInformation;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Component;

class UpdateAccountInformationForm extends Component
{
    /**
     * The user instance.
     *
     * @var User
     */
    public User $user;

    /**
     * The component's state.
     *
     * @var array
     */
    public array $state = [];

    /**
     * The component's listeners.
     *
     * @var array
     */
    protected $listeners = [
        'refresh-component' => '$refresh'
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
        $this->user = $user;
        $state = $user->only(['slug', 'email']);

        $this->state = [
            'username' => $state['slug'],
            'email' => $state['email']
        ];
    }

    /**
     * Update the user's account information.
     *
     * @param UpdatesUserAccountInformation $updater
     * @return void
     */
    public function updateAccountInformation(UpdatesUserAccountInformation $updater): void
    {
        $this->resetErrorBag();

        $attributes = $this->state;

        $updater->update($this->user, $attributes);

        $this->dispatch('saved');
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.update-account-information-form');
    }
}
