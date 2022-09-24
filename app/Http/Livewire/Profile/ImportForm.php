<?php

namespace App\Http\Livewire\Profile;

use App\Contracts\Web\Profile\ImportsUserAnimeLibrary;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class ImportForm extends Component
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
     * @param ImportsUserAnimeLibrary $updater
     */
    public function importAnimeLibrary(ImportsUserAnimeLibrary $updater)
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
        return view('livewire.profile.import-form');
    }
}
