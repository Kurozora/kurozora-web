<?php

namespace App\Models;

use App\Enums\ExploreCategoryTypes;
use App\Scopes\ExploreCategoryIsEnabledScope;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Request;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class ExploreCategory extends KModel implements Sitemapable
{
    use HasSlug,
        SoftDeletes;

    // Table name
    const TABLE_NAME = 'explore_categories';
    protected $table = self::TABLE_NAME;

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'explore_category_items'
    ];

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        if (app('explore.only_enabled')) {
            static::addGlobalScope(new ExploreCategoryIsEnabledScope);
        }
    }

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
     * @param Genre|Theme|null $model
     * @return ExploreCategory
     */
    public function most_popular_shows(Genre|Theme|null $model = null): ExploreCategory
    {
        if ($this->type === ExploreCategoryTypes::MostPopularShows) {
            if (is_a($model, Genre::class)) {
                $popularShows = Anime::whereGenre($model)
                    ->mostPopular(10, 3, $model->is_nsfw) // fucking named parameters not working
                    ->get('id');
            } else if (is_a($model, Theme::class)) {
                $popularShows = Anime::whereTheme($model)
                    ->mostPopular(10, 3, $model->is_nsfw) // look above
                    ->get('id');
            } else {
                $popularShows = Anime::mostPopular()->get('id');
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
     * @param Genre|Theme|null $model
     * @return ExploreCategory
     */
    public function upcoming_shows(Genre|Theme|null $model = null): ExploreCategory
    {
        if ($this->type === ExploreCategoryTypes::UpcomingShows) {
            if (is_a($model, Genre::class)) {
                $upcomingShows = Anime::whereGenre($model)
                    ->upcomingShows()
                    ->get('id');
            } else if (is_a($model, Theme::class)) {
                $upcomingShows = Anime::whereTheme($model)
                    ->upcomingShows()
                    ->get('id');
            } else {
                $upcomingShows = Anime::upcomingShows()
                    ->get('id');
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
     * Append the shows continuing since past season(s) to the category's items.
     *
     * @param Genre|Theme|null $model
     * @return ExploreCategory
     */
    public function anime_continuing(Genre|Theme|null $model = null): ExploreCategory
    {
        if ($this->type === ExploreCategoryTypes::AnimeContinuing) {
            if (is_a($model, Genre::class)) {
                $animeContinuing = Anime::whereGenre($model)
                    ->animeContinuing()
                    ->get('id');
            } else if (is_a($model, Theme::class)) {
                $animeContinuing = Anime::whereTheme($model)
                    ->animeContinuing()
                    ->get('id');
            } else {
                $animeContinuing = Anime::animeContinuing()
                    ->get('id');
            }

            foreach($animeContinuing as $anime) {
                $this->explore_category_items->add(new ExploreCategoryItem([
                    'model_id' => $anime->id,
                    'model_type' => get_class($anime)
                ]));
            }
        }
        return $this;
    }

    /**
     * Append the shows of the current season to the category's items.
     *
     * @param Genre|Theme|null $model
     * @return ExploreCategory
     */
    public function anime_season(Genre|Theme|null $model = null): ExploreCategory
    {
        if ($this->type === ExploreCategoryTypes::AnimeSeason) {
            if (is_a($model, Genre::class)) {
                $animeSeason = Anime::whereGenre($model)
                    ->animeSeason()
                    ->get('id');
            } else if (is_a($model, Theme::class)) {
                $animeSeason = Anime::whereTheme($model)
                    ->animeSeason()
                    ->get('id');
            } else {
                $animeSeason = Anime::animeSeason()
                    ->get('id');
            }

            foreach($animeSeason as $anime) {
                $this->explore_category_items->add(new ExploreCategoryItem([
                    'model_id' => $anime->id,
                    'model_type' => get_class($anime)
                ]));
            }
        }
        return $this;
    }

    /**
     * Returns the current most popular anime.
     *
     * @param Genre|Theme $model
     * @return ExploreCategory
     */
    public function shows_we_love(Genre|Theme $model): ExploreCategory
    {
        if ($this->type === ExploreCategoryTypes::Shows) {
            if (is_a($model, Genre::class)) {
                $randomShows = $model->animes()
                    ->inRandomOrder()
                    ->limit(10)
                    ->get('id');
            } else if (is_a($model, Theme::class)) {
                $randomShows = $model->animes()
                    ->inRandomOrder()
                    ->limit(10)
                    ->get('id');
            } else {
                $randomShows = Anime::inRandomOrder()
                    ->limit(10)
                    ->get('id');
            }

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
     * @param int $limit
     * @return ExploreCategory
     */
    public function charactersBornToday(int $limit = 10): ExploreCategory
    {
        if ($this->type === ExploreCategoryTypes::Characters) {
            $charactersBornToday = Character::bornToday($limit)->get('id');

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
     * @param int $limit
     * @return ExploreCategory
     */
    public function peopleBornToday(int $limit = 10): ExploreCategory
    {
        if ($this->type === ExploreCategoryTypes::People) {
            $peopleBornToday = Person::bornToday($limit)->get('id');

            foreach($peopleBornToday as $personBornToday) {
                $this->explore_category_items->add(new ExploreCategoryItem([
                    'model_id' => $personBornToday->id,
                    'model_type' => get_class($personBornToday)
                ]));
            }
        }
        return $this;
    }

    /**
     * Convert the model to its sitemap representation.
     *
     * @return Url|string|array
     */
    public function toSitemapTag(): Url|string|array
    {
        return Url::create(route('explore.details', $this))
            ->setChangeFrequency('weekly');
    }
}
