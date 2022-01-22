<?php

namespace App\Http\Livewire\Profile;

use App\Contracts\Web\Profile\ImportsUserLibraryFromMAL;
use Auth;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class MalImportForm extends Component
{
    use WithFileUploads;

    /**
     * The component's state.
     *
     * @var array
     */
    public array $state = [];

    /**
     * Update the user's preferred TV rating.
     *
     * @param ImportsUserLibraryFromMAL $updater
     */
    public function importLibraryFromMAL(ImportsUserLibraryFromMAL $updater)
    {
        $this->resetErrorBag();

        $updater->update(Auth::user(), $this->state);

        $this->emit('saved');
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.mal-import-form');
    }
}
