<?php

namespace App\Models;

use App\Enums\ExploreCategoryTypes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExploreCategory extends KModel
{
    // Table name
    const TABLE_NAME = 'explore_categories';
    protected $table = self::TABLE_NAME;

    /**
     * The items of the explore category.
     *
     * @return HasMany
     */
    function explore_category_items(): HasMany
    {
        return $this->hasMany(ExploreCategoryItem::class);
    }

    /**
     * Returns the current most popular anime.
     *
     * @param Genre|null $genre
     * @return ExploreCategory
     */
    public function most_popular_anime(?Genre $genre = null): ExploreCategory
    {
        if ($this->type === ExploreCategoryTypes::MostPopularShows) {
            if (empty($genre)) {
                $popularShows = Anime::mostPopular()->get();
            } else {
                $popularShows = $genre->animes()->mostPopular()->get();
            }

            foreach($popularShows as $popularShow) {
                $this->explore_category_items->add($popularShow);
            }
        }
        return $this;
    }

    /**
     * Returns the current most popular anime.
     *
     * @param Genre $genre
     * @return ExploreCategory
     */
    public function shows_we_love(Genre $genre): ExploreCategory
    {
        if ($this->type === ExploreCategoryTypes::Shows) {
            $randomShows = $genre->animes()
                ->inRandomOrder()
                ->limit(10)
                ->get();

            foreach($randomShows as $randomShow) {
                $this->explore_category_items->add($randomShow);
            }
        }
        return $this;
    }

    /**
     * Append the characters born today to the category's items.
     *
     * @return ExploreCategory
     */
    public function charactersBornToday(): ExploreCategory
    {
        if ($this->type === ExploreCategoryTypes::Characters) {
            $charactersBornToday = Character::bornToday()->get();

            foreach($charactersBornToday as $characterBornToday) {
                $this->explore_category_items->add($characterBornToday);
            }
        }
        return $this;
    }

    /**
     * Append the people born today to the category's items
     *
     * @return ExploreCategory
     */
    public function peopleBornToday(): ExploreCategory
    {
        if ($this->type === ExploreCategoryTypes::People) {
            $peopleBornToday = Person::bornToday()->get();

            foreach($peopleBornToday as $personBornToday) {
                $this->explore_category_items->add($personBornToday);
            }
        }
        return $this;
    }
}
