<?php

namespace App\Http\Livewire;

use App\Models\ExploreCategory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Home extends Component
{
    /**
     * The object containing the collection of explore category data.
     *
     * @var ExploreCategory[]|Collection $exploreCategories
     */
    public array|Collection $exploreCategories;

    /**
     * Prepare the component.
     *
     * @return void
     */
    function mount()
    {
        $this->exploreCategories = ExploreCategory::orderBy('position')->get();;
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.home');
    }
}
