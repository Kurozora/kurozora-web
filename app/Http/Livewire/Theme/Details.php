<?php

namespace App\Http\Livewire\Theme;

use App\Models\ExploreCategory;
use App\Models\Theme;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Details extends Component
{
    /**
     * The object containing the theme data.
     *
     * @var Theme $theme
     */
    public Theme $theme;

    /**
     * The object containing the collection of explore category data.
     *
     * @var ExploreCategory[]|Collection $exploreCategories
     */
    public array|Collection $exploreCategories;

    /**
     * Prepare the component.
     *
     * @param Theme $theme
     * @return void
     */
    public function mount(Theme $theme): void
    {
        $this->theme = $theme;
        $this->exploreCategories = ExploreCategory::where('is_global', true)
            ->orderBy('position')
            ->get();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.theme.details');
    }
}
