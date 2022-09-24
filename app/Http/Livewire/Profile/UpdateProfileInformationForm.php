<?php

namespace App\Http\Livewire\Profile;

use App\Contracts\UpdatesUserProfileInformation;
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
        $this->state = auth()->user()->withoutRelations()->toArray();
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

        $this->emit('saved');

        if (isset($this->profileImage)) {
            $this->emit('refresh-profile-image');
            $this->emit('refresh-navigation-dropdown');
            $this->profileImage = null;
        }

        if (isset($this->bannerImage)) {
            $this->emit('refresh-banner-image');
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
        auth()->user()->deleteProfileImage();

        $this->emitSelf('refresh-component');
        $this->emit('refresh-profile-image');
        $this->emit('refresh-navigation-dropdown');
    }

    /**
     * Delete user's banner image.
     *
     * @return void
     */
    public function deleteBannerImage(): void
    {
        auth()->user()->deleteBannerImage();

        $this->emitSelf('refresh-component');
        $this->emit('refresh-banner-image');
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
