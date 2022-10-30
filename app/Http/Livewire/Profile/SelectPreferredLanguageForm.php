<?php

namespace App\Http\Livewire\Profile;

use App\Contracts\Web\Profile\UpdatesUserPreferredLanguage;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Component;

class SelectPreferredLanguageForm extends Component
{
    /**
     * The component's state.
     *
     * @var array
     */
    public array $state = [];

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount(): void
    {
        $this->state = [
            'language' => auth()->user()->language_id
        ];
    }

    /**
     * Update the user's preferred language.
     *
     * @param UpdatesUserPreferredLanguage $updater
     */
    public function updatePreferredLanguage(UpdatesUserPreferredLanguage $updater)
    {
        $this->resetErrorBag();

        $updater->update(auth()->user(), $this->state);

        $this->emit('saved');
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
