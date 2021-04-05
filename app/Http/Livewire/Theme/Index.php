<?php

namespace App\Http\Livewire\Theme;

use App\Models\AppTheme;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render()
    {
        return view('livewire.theme.index', [
            'themes' => AppTheme::paginate(5),
        ]);
    }
}
