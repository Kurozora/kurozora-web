<?php

namespace App\Http\Livewire\Genre;

use App\Enums\ExplorePageCategoryTypes;
use App\Models\ExplorePageCategory;
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
     * @var array|Collection $explorePageCategories
     */
    public array|Collection $explorePageCategories;

    /**
     * Prepare the component.
     *
     * @param Genre $genre
     * @return void
     */
    public function mount(Genre $genre)
    {
        $this->genre = $genre;
        $this->explorePageCategories = $this->getCategoriesForGenre($genre);
    }

    /**
     * Generates fixed explore page categories for a specific genre.
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
     * Returns the EPC for featured shows for a specific genre.
     *
     * @param Genre $genre
     * @param int $position
     * @return ExplorePageCategory
     */
    private function getFeaturedShowsCategoryForGenre(Genre $genre, int $position): ExplorePageCategory
    {
        $category = ExplorePageCategory::make([
            'title'     => 'Featured ' . $genre->name . ' Shows',
            'position'  => $position,
            'type'      => ExplorePageCategoryTypes::MostPopularShows,
            'size'      => 'large'
        ]);

        $popularShows = $genre->animes()->mostPopular(10)->get();

        foreach($popularShows as $popularShow) {
            $category->animes->add($popularShow);
        }

        return $category;
    }

    /**
     * Returns the EPC for shows we love of a specific genre.
     *
     * @param Genre $genre
     * @param int $position
     * @return ExplorePageCategory
     */
    private function getShowsWeLoveCategoryForGenre(Genre $genre, int $position): ExplorePageCategory
    {
        $category = ExplorePageCategory::make([
            'title'     => $genre->name . ' Shows We Love',
            'position'  => $position,
            'type'      => ExplorePageCategoryTypes::Shows,
            'size'      => 'video'
        ]);

        $randomShows = $genre->animes()
            ->inRandomOrder()
            ->limit(10)
            ->get();

        foreach($randomShows as $randomShow) {
            $category->animes->add($randomShow);
        }

        return $category;
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
