<?php

namespace App\Http\Livewire\Components;

use App\Enums\ExploreCategoryTypes;
use App\Models\Character;
use App\Models\ExploreCategory;
use App\Models\ExploreCategoryItem;
use App\Models\Person;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class ExploreCategorySection extends Component
{
    /**
     * The object containing the explore category data.
     *
     * @var ExploreCategory $exploreCategory
     */
    public ExploreCategory $exploreCategory;

    /**
     * The array containing the explore category item data.
     *
     * @var ExploreCategoryItem[] $exploreCategoryItems
     */
    public array|Collection $exploreCategoryItems = [];

    /**
     * The number of items the explore category has.
     *
     * @var int $exploreCategoryCount
     */
    public int $exploreCategoryCount = 0;

    /**
     * Prepare the component.
     *
     * @param ExploreCategory $exploreCategory
     * @return void
     */
    public function mount(ExploreCategory $exploreCategory)
    {
        $this->exploreCategory = $exploreCategory;
        $this->exploreCategoryCount = match ($exploreCategory->type) {
            ExploreCategoryTypes::Characters => Character::bornToday()->count(),
            ExploreCategoryTypes::People => Person::bornToday()->count(),
            default => $exploreCategory->explore_category_items()->count()
        };
    }

    /**
     * Loads the explore category section.
     *
     * @return void
     */
    public function loadExploreCategoryItems()
    {
        $this->exploreCategoryItems = match ($this->exploreCategory->type) {
            ExploreCategoryTypes::Characters => $this->exploreCategory->charactersBornToday()->explore_category_items,
            ExploreCategoryTypes::People => $this->exploreCategory->peopleBornToday()->explore_category_items,
            default => $this->exploreCategory->explore_category_items
        };
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.explore-category-section');
    }
}
