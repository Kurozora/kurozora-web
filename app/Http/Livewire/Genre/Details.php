<?php

namespace App\Http\Livewire\Genre;

use App\Enums\ExploreCategoryTypes;
use App\Models\ExploreCategory;
use App\Models\Genre;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Details extends Component
{
    /**
     * The object containing the genre data.
     *
     * @var Genre $genre
     */
    public Genre $genre;

    /**
     * The object containing the collection of explore category data.
     *
     * @var ExploreCategory[]|Collection $exploreCategories
     */
    public array|Collection $exploreCategories;

    /**
     * Prepare the component.
     *
     * @param Genre $genre
     * @return void
     */
    public function mount(Genre $genre)
    {
        $this->genre = $genre;
        $this->exploreCategories = $this->getCategoriesForGenre($genre);
    }

    /**
     * Generates fixed explore categories for a specific genre.
     *
     * @param Genre $genre
     * @return array
     */
    private function getCategoriesForGenre(Genre $genre): array
    {
        $categories = [];

        $categories[] = $this->getFeaturedShowsCategoryForGenre($genre, 1);
        $categories[] = $this->getShowsWeLoveCategoryForGenre($genre, 2);

        return $categories;
    }

    /**
     * Returns the explore category for featured shows for a specific genre.
     *
     * @param Genre $genre
     * @param int $position
     * @return ExploreCategory
     */
    private function getFeaturedShowsCategoryForGenre(Genre $genre, int $position): ExploreCategory
    {
        $exploreCategory = ExploreCategory::make([
            'title'     => 'Featured ' . $genre->name . ' Shows',
            'position'  => $position,
            'type'      => ExploreCategoryTypes::MostPopularShows,
            'size'      => 'large'
        ]);
        return $exploreCategory->most_popular_anime($genre);
    }

    /**
     * Returns the explore category for shows we love of a specific genre.
     *
     * @param Genre $genre
     * @param int $position
     * @return ExploreCategory
     */
    private function getShowsWeLoveCategoryForGenre(Genre $genre, int $position): ExploreCategory
    {
        $category = ExploreCategory::make([
            'title'     => $genre->name . ' Shows We Love',
            'position'  => $position,
            'type'      => ExploreCategoryTypes::Shows,
            'size'      => 'video'
        ]);
        return $category->shows_we_love($genre);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.genre.details')
            ->layout('layouts.base');
    }
}
