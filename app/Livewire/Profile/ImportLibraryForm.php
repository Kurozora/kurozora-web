<?php

namespace App\Livewire\Profile;

use App\Contracts\Web\Profile\ImportsUserLibrary;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class ImportLibraryForm extends Component
{
    use WithFileUploads;

    /**
     * The component's state.
     *
     * @var array
     */
    public array $state = [];

    /**
     * Import anime to the user's library.
     *
     * @param ImportsUserLibrary $updater
     */
    public function importUserLibrary(ImportsUserLibrary $updater): void
    {
        $this->resetErrorBag();

        $updater->update(auth()->user(), $this->state);

        $this->dispatch('saved');
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.import-library-form');
    }
}
