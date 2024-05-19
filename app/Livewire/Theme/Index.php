<?php

namespace App\Livewire\Theme;

use App\Models\Theme;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Index extends Component
{
    /**
     * The object containing the collection of themes.
     *
     * @var Collection|Theme[] $themes
     */
    public Collection|array $themes;

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount()
    {
        $this->themes = Theme::orderBy('name')
            ->get();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.theme.index');
    }
}
