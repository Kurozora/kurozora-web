<?php

namespace App\Models;

use App\Enums\ExploreCategoryTypes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Request;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class ExploreCategory extends KModel
{
    use HasSlug;

    // Table name
    const TABLE_NAME = 'explore_categories';
    protected $table = self::TABLE_NAME;

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        if (Request::wantsJson()) {
            return parent::getRouteKeyName();
        }
        return 'slug';
    }

    /**
     * Get the options for generating the slug.
     *
     * @return SlugOptions
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

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
    public function most_popular_shows(?Genre $genre = null): ExploreCategory
    {
        if ($this->type === ExploreCategoryTypes::MostPopularShows) {
            if (empty($genre)) {
                $popularShows = Anime::mostPopular()->get();
            } else {
                $popularShows = $genre->animes()->mostPopular()->get();
            }

            foreach($popularShows as $popularShow) {
                $this->explore_category_items->add(new ExploreCategoryItem([
                    'model_id' => $popularShow->id,
                    'model_type' => get_class($popularShow)
                ]));
            }
        }
        return $this;
    }

    /**
     * Returns the upcoming anime.
     *
     * @param Genre|null $genre
     * @return ExploreCategory
     */
    public function upcoming_shows(?Genre $genre = null): ExploreCategory
    {
        if ($this->type === ExploreCategoryTypes::UpcomingShows) {
            if (empty($genre)) {
                $upcomingShows = Anime::upcomingShows()->get();
            } else {
                $upcomingShows = $genre->animes()->upcomingShows()->get();
            }

            foreach($upcomingShows as $upcomingShow) {
                $this->explore_category_items->add(new ExploreCategoryItem([
                    'model_id' => $upcomingShow->id,
                    'model_type' => get_class($upcomingShow)
                ]));
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
                $this->explore_category_items->add(new ExploreCategoryItem([
                    'model_id' => $randomShow->id,
                    'model_type' => get_class($randomShow)
                ]));
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
                $this->explore_category_items->add(new ExploreCategoryItem([
                    'model_id' => $characterBornToday->id,
                    'model_type' => get_class($characterBornToday)
                ]));
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
                $this->explore_category_items->add(new ExploreCategoryItem([
                    'model_id' => $personBornToday->id,
                    'model_type' => get_class($personBornToday)
                ]));
            }
        }
        return $this;
    }
}
