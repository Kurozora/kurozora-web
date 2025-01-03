<?php

namespace App\Livewire\Profile;

use App\Contracts\UpdatesUserProfileInformation;
use App\Enums\MediaCollection;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class UpdateProfileInformationForm extends Component
{
    use WithFileUploads;

    /**
     * The component's state.
     *
     * @var array
     */
    public array $state = [];

    /**
     * The new profile image for the user.
     *
     * @var mixed
     */
    public mixed $profileImage = null;

    /**
     * The new banner image for the user.
     *
     * @var mixed
     */
    public mixed $bannerImage = null;

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
     * @return void
     */
    public function mount(): void
    {
        $state = auth()->user()
            ->only(['username', 'biography']);

        $this->state = [
            'nickname' => $state['username'],
            'biography' => $state['biography']
        ];
    }

    /**
     * Get the current user of the application.
     *
     * @return User|null
     */
    public function getUserProperty(): User|null
    {
        return auth()->user()
            ->load(['media']);
    }

    /**
     * Update the user's profile information.
     *
     * @param UpdatesUserProfileInformation $updater
     * @return void
     */
    public function updateProfileInformation(UpdatesUserProfileInformation $updater): void
    {
        $this->resetErrorBag();

        $attributes = $this->state;

        if (!empty($this->profileImage)) {
            $attributes = array_merge($attributes, ['profileImage' => $this->profileImage]);
        }

        if (!empty($this->bannerImage)) {
            $attributes = array_merge($attributes, ['bannerImage' => $this->bannerImage]);
        }

        $updater->update(auth()->user(), $attributes);

        $this->dispatch('saved');

        if (isset($this->profileImage)) {
            $this->dispatch('refresh-profile-image');
            $this->dispatch('refresh-navigation-dropdown');
            $this->profileImage = null;
        }

        if (isset($this->bannerImage)) {
            $this->dispatch('refresh-banner-image');
            $this->bannerImage = null;
        }
    }

    /**
     * Delete user's profile image.
     *
     * @return void
     */
    public function deleteProfileImage(): void
    {
        auth()->user()->clearMediaCollection(MediaCollection::Profile);

        $this->dispatch('refresh-component')->self();
        $this->dispatch('refresh-profile-image');
        $this->dispatch('refresh-navigation-dropdown');
    }

    /**
     * Delete user's banner image.
     *
     * @return void
     */
    public function deleteBannerImage(): void
    {
        auth()->user()->clearMediaCollection(MediaCollection::Banner);

        $this->dispatch('refresh-component')->self();
        $this->dispatch('refresh-banner-image');
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.update-profile-information-form');
    }
}
