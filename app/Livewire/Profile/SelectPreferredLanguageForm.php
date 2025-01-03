<?php

namespace App\Livewire\Profile;

use App\Contracts\Web\Profile\UpdatesUserPreferredLanguage;
use App\Models\Language;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class SelectPreferredLanguageForm extends Component
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
     * Determines whether to load the section.
     *
     * @var bool $readyToLoad
     */
    public $readyToLoad = false;

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
        $this->state = [
            'language' => $user->language_id
        ];
    }

    /**
     * Update the user's preferred language.
     *
     * @param UpdatesUserPreferredLanguage $updater
     */
    public function updatePreferredLanguage(UpdatesUserPreferredLanguage $updater): void
    {
        $this->resetErrorBag();

        $updater->update($this->user, $this->state);

        $this->dispatch('saved');
    }

    /**
     * Sets the property to load the section.
     *
     * @return void
     */
    public function loadSection(): void
    {
        $this->readyToLoad = true;
    }

    /**
     * Get the languages.
     *
     * @return Collection
     */
    public function getLanguagesProperty(): Collection
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return Language::all();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.select-preferred-language-form');
    }
}
