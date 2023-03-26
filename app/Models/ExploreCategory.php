<?php

namespace App\Models;

use App\Enums\ExploreCategoryTypes;
use App\Scopes\ExploreCategoryIsEnabledScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Request;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class ExploreCategory extends KModel implements Sitemapable, Sortable
{
    use HasSlug,
        SoftDeletes,
        SortableTrait;

    // Table name
    const TABLE_NAME = 'explore_categories';
    protected $table = self::TABLE_NAME;

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'exploreCategoryItems'
    ];

    /**
     * The sortable configurations.
     *
     * @var array
     */
    public array $sortable = [
        'order_column_name' => 'position',
        'sort_when_creating' => true,
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
     * The query used for sorting.
     *
     * @return Builder
     */
    public function buildSortQuery(): Builder
    {
        return static::query()->withoutGlobalScopes();
    }

    /**
     * The items of the explore category.
     *
     * @return HasMany
     */
    function exploreCategoryItems(): HasMany
    {
        return $this->hasMany(ExploreCategoryItem::class)
            ->orderBy('position');
    }

    function anime(Genre|Theme|null $model = null): Anime|Builder
    {
        if (is_a($model, Genre::class)) {
            return Anime::whereGenre($model);
        } else if (is_a($model, Theme::class)) {
            return Anime::whereTheme($model);
        }

        return Anime::query();
    }

    function manga(Genre|Theme|null $model = null): Manga|Builder
    {
        if (is_a($model, Genre::class)) {
            return Manga::whereGenre($model);
        } else if (is_a($model, Theme::class)) {
            return Manga::whereTheme($model);
        }

        return Manga::query();
    }

    function game(Genre|Theme|null $model = null): Game|Builder
    {
        if (is_a($model, Genre::class)) {
            return Game::whereGenre($model);
        } else if (is_a($model, Theme::class)) {
            return Game::whereTheme($model);
        }

        return Game::query();
    }

    /**
     * Returns the current most popular anime.
     *
     * @param Genre|Theme|null $model
     * @return ExploreCategory
     */
    public function mostPopularShows(Genre|Theme|null $model = null): ExploreCategory
    {
        if ($this->type === ExploreCategoryTypes::MostPopularShows) {
            $popularShows = $this->anime($model)
                ->mostPopular(10, 3, (bool) $model?->is_nsfw)
                ->get();

            foreach($popularShows as $popularShow) {
                $this->exploreCategoryItems->add(new ExploreCategoryItem([
                    'model_id' => $popularShow->id,
                    'model_type' => $popularShow->getMorphClass()
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
    public function upcomingShows(Genre|Theme|null $model = null): ExploreCategory
    {
        if ($this->type === ExploreCategoryTypes::UpcomingShows) {
            $upcomingShows = $this->anime($model)
                ->upcomingShows()
                ->get(Anime::TABLE_NAME . '.id');

            foreach($upcomingShows as $upcomingShow) {
                $this->exploreCategoryItems->add(new ExploreCategoryItem([
                    'model_id' => $upcomingShow->id,
                    'model_type' => $upcomingShow->getMorphClass()
                ]));
            }
        }
        return $this;
    }

    /**
     * Returns anime that's been added recently.
     *
     * @param Genre|Theme|null $model
     * @param int $limit
     * @return ExploreCategory
     */
    public function newShows(Genre|Theme|null $model = null, int $limit = 10): ExploreCategory
    {
        if ($this->type === ExploreCategoryTypes::NewShows) {
            $newShows = $this->anime($model)
                ->newShows($limit)
                ->get(Anime::TABLE_NAME . '.id');

            foreach($newShows as $newShow) {
                $this->exploreCategoryItems->add(new ExploreCategoryItem([
                    'model_id' => $newShow->id,
                    'model_type' => $newShow->getMorphClass()
                ]));
            }
        }
        return $this;
    }

    /**
     * Returns anime that's been added recently.
     *
     * @param Genre|Theme|null $model
     * @param int $limit
     * @return ExploreCategory
     */
    public function recentlyUpdatedShows(Genre|Theme|null $model = null, int $limit = 10): ExploreCategory
    {
        if ($this->type === ExploreCategoryTypes::RecentlyUpdateShows) {
            $recentlyUpdatedShows = $this->anime($model)
                ->recentlyUpdatedShows($limit)
                ->get(Anime::TABLE_NAME . '.id');

            foreach($recentlyUpdatedShows as $recentlyUpdatedShow) {
                $this->exploreCategoryItems->add(new ExploreCategoryItem([
                    'model_id' => $recentlyUpdatedShow->id,
                    'model_type' => $recentlyUpdatedShow->getMorphClass()
                ]));
            }
        }
        return $this;
    }

    /**
     * Returns anime that's finished recently.
     *
     * @param Genre|Theme|null $model
     * @param int $limit
     * @return ExploreCategory
     */
    public function recentlyFinishedShows(Genre|Theme|null $model = null, int $limit = 10): ExploreCategory
    {
        if ($this->type === ExploreCategoryTypes::RecentlyFinishedShows) {
            $recentlyFinishedShows = $this->anime($model)
                ->recentlyFinishedShows($limit)
                ->get(Anime::TABLE_NAME . '.id');

            foreach($recentlyFinishedShows as $recentlyFinishedShow) {
                $this->exploreCategoryItems->add(new ExploreCategoryItem([
                    'model_id' => $recentlyFinishedShow->id,
                    'model_type' => $recentlyFinishedShow->getMorphClass()
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
    public function animeContinuing(Genre|Theme|null $model = null): ExploreCategory
    {
        if ($this->type === ExploreCategoryTypes::ContinuingShows) {
            $animeContinuing = $this->anime($model)
                ->animeContinuing()
                ->get(Anime::TABLE_NAME . '.id');

            foreach($animeContinuing as $anime) {
                $this->exploreCategoryItems->add(new ExploreCategoryItem([
                    'model_id' => $anime->id,
                    'model_type' => $anime->getMorphClass()
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
    public function animeSeason(Genre|Theme|null $model = null): ExploreCategory
    {
        if ($this->type === ExploreCategoryTypes::ShowsSeason) {
            $animeSeason = $this->anime($model)
                ->animeSeason()
                ->get(Anime::TABLE_NAME . '.id');

            foreach($animeSeason as $anime) {
                $this->exploreCategoryItems->add(new ExploreCategoryItem([
                    'model_id' => $anime->id,
                    'model_type' => $anime->getMorphClass()
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
    public function showsWeLove(Genre|Theme $model): ExploreCategory
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
                $this->exploreCategoryItems->add(new ExploreCategoryItem([
                    'model_id' => $randomShow->id,
                    'model_type' => $randomShow->getMorphClass()
                ]));
            }
        }
        return $this;
    }

    /**
     * Returns the current most popular manga.
     *
     * @param Genre|Theme|null $model
     * @return ExploreCategory
     */
    public function mostPopularLiterature(Genre|Theme|null $model = null): ExploreCategory
    {
        if ($this->type === ExploreCategoryTypes::MostPopularLiteratures) {
            $popularManga = $this->manga($model)
                ->mostPopular(10, 3, $model->is_nsfw)
                ->get(Manga::TABLE_NAME . '.id');

            foreach($popularManga as $manga) {
                $this->exploreCategoryItems->add(new ExploreCategoryItem([
                    'model_id' => $manga->id,
                    'model_type' => $manga->getMorphClass()
                ]));
            }
        }
        return $this;
    }

    /**
     * Returns the upcoming manga.
     *
     * @param Genre|Theme|null $model
     * @return ExploreCategory
     */
    public function upcomingLiterature(Genre|Theme|null $model = null): ExploreCategory
    {
        if ($this->type === ExploreCategoryTypes::UpcomingLiteratures) {
            $upcomingManga = $this->manga($model)
                ->upcomingManga()
                ->get(Manga::TABLE_NAME . '.id');

            foreach($upcomingManga as $manga) {
                $this->exploreCategoryItems->add(new ExploreCategoryItem([
                    'model_id' => $manga->id,
                    'model_type' => $manga->getMorphClass()
                ]));
            }
        }
        return $this;
    }

    /**
     * Returns manga that's been added recently.
     *
     * @param Genre|Theme|null $model
     * @param int $limit
     * @return ExploreCategory
     */
    public function newLiterature(Genre|Theme|null $model = null, int $limit = 10): ExploreCategory
    {
        if ($this->type === ExploreCategoryTypes::NewLiteratures) {
            $newManga = $this->manga($model)
                ->newManga($limit)
                ->get(Manga::TABLE_NAME . '.id');

            foreach($newManga as $newShow) {
                $this->exploreCategoryItems->add(new ExploreCategoryItem([
                    'model_id' => $newShow->id,
                    'model_type' => $newShow->getMorphClass()
                ]));
            }
        }
        return $this;
    }

    /**
     * Returns manga that's been added recently.
     *
     * @param Genre|Theme|null $model
     * @param int $limit
     * @return ExploreCategory
     */
    public function recentlyUpdatedLiterature(Genre|Theme|null $model = null, int $limit = 10): ExploreCategory
    {
        if ($this->type === ExploreCategoryTypes::RecentlyUpdateLiteratures) {
            $recentlyUpdatedManga = $this->manga($model)
                ->recentlyUpdatedManga($limit)
                ->get(Manga::TABLE_NAME . '.id');

            foreach($recentlyUpdatedManga as $recentlyUpdatedShow) {
                $this->exploreCategoryItems->add(new ExploreCategoryItem([
                    'model_id' => $recentlyUpdatedShow->id,
                    'model_type' => $recentlyUpdatedShow->getMorphClass()
                ]));
            }
        }
        return $this;
    }

    /**
     * Returns manga that's finished recently.
     *
     * @param Genre|Theme|null $model
     * @param int $limit
     * @return ExploreCategory
     */
    public function recentlyFinishedLiterature(Genre|Theme|null $model = null, int $limit = 10): ExploreCategory
    {
        if ($this->type === ExploreCategoryTypes::RecentlyFinishedLiteratures) {
            $recentlyFinishedManga = $this->manga($model)
                ->recentlyFinishedManga($limit)
                ->get(Manga::TABLE_NAME . '.id');

            foreach($recentlyFinishedManga as $recentlyFinishedShow) {
                $this->exploreCategoryItems->add(new ExploreCategoryItem([
                    'model_id' => $recentlyFinishedShow->id,
                    'model_type' => $recentlyFinishedShow->getMorphClass()
                ]));
            }
        }
        return $this;
    }

    /**
     * Append the manga continuing since past season(s) to the category's items.
     *
     * @param Genre|Theme|null $model
     * @return ExploreCategory
     */
    public function literatureContinuing(Genre|Theme|null $model = null): ExploreCategory
    {
        if ($this->type === ExploreCategoryTypes::ContinuingLiteratures) {
            $mangaContinuing = $this->manga($model)
                ->mangaContinuing()
                ->get(Manga::TABLE_NAME . '.id');

            foreach($mangaContinuing as $manga) {
                $this->exploreCategoryItems->add(new ExploreCategoryItem([
                    'model_id' => $manga->id,
                    'model_type' => $manga->getMorphClass()
                ]));
            }
        }
        return $this;
    }

    /**
     * Append the manga of the current season to the category's items.
     *
     * @param Genre|Theme|null $model
     * @return ExploreCategory
     */
    public function literatureSeason(Genre|Theme|null $model = null): ExploreCategory
    {
        if ($this->type === ExploreCategoryTypes::LiteraturesSeason) {
            $mangaSeason = $this->manga($model)
                ->mangaSeason()
                ->get(Manga::TABLE_NAME . '.id');

            foreach($mangaSeason as $manga) {
                $this->exploreCategoryItems->add(new ExploreCategoryItem([
                    'model_id' => $manga->id,
                    'model_type' => $manga->getMorphClass()
                ]));
            }
        }
        return $this;
    }

    /**
     * Returns the current most popular manga.
     *
     * @param Genre|Theme $model
     * @return ExploreCategory
     */
    public function literatureWeLove(Genre|Theme $model): ExploreCategory
    {
        if ($this->type === ExploreCategoryTypes::Literatures) {
            if (is_a($model, Genre::class)) {
                $randomManga = $model->mangas()
                    ->inRandomOrder()
                    ->limit(10)
                    ->get('id');
            } else if (is_a($model, Theme::class)) {
                $randomManga = $model->mangas()
                    ->inRandomOrder()
                    ->limit(10)
                    ->get('id');
            } else {
                $randomManga = Manga::inRandomOrder()
                    ->limit(10)
                    ->get('id');
            }

            foreach($randomManga as $manga) {
                $this->exploreCategoryItems->add(new ExploreCategoryItem([
                    'model_id' => $manga->id,
                    'model_type' => $manga->getMorphClass()
                ]));
            }
        }
        return $this;
    }

    /**
     * Returns the current most popular game.
     *
     * @param Genre|Theme|null $model
     * @return ExploreCategory
     */
    public function mostPopularGames(Genre|Theme|null $model = null): ExploreCategory
    {
        if ($this->type === ExploreCategoryTypes::MostPopularGames) {
            $popularGame = $this->game($model)
                ->mostPopular(10, 3, $model->is_nsfw)
                ->get(Game::TABLE_NAME . '.id');

            foreach($popularGame as $game) {
                $this->exploreCategoryItems->add(new ExploreCategoryItem([
                    'model_id' => $game->id,
                    'model_type' => $game->getMorphClass()
                ]));
            }
        }
        return $this;
    }

    /**
     * Returns the upcoming games.
     *
     * @param Genre|Theme|null $model
     * @return ExploreCategory
     */
    public function upcomingGames(Genre|Theme|null $model = null): ExploreCategory
    {
        if ($this->type === ExploreCategoryTypes::UpcomingGames) {
            $upcomingGame = $this->game($model)
                ->upcomingGames()
                ->get(Game::TABLE_NAME . '.id');

            foreach($upcomingGame as $game) {
                $this->exploreCategoryItems->add(new ExploreCategoryItem([
                    'model_id' => $game->id,
                    'model_type' => $game->getMorphClass()
                ]));
            }
        }
        return $this;
    }

    /**
     * Returns game that's been added recently.
     *
     * @param Genre|Theme|null $model
     * @param int $limit
     * @return ExploreCategory
     */
    public function newGames(Genre|Theme|null $model = null, int $limit = 10): ExploreCategory
    {
        if ($this->type === ExploreCategoryTypes::NewGames) {
            $newGame = $this->game($model)
                ->newGames($limit)
                ->get(Game::TABLE_NAME . '.id');

            foreach($newGame as $newShow) {
                $this->exploreCategoryItems->add(new ExploreCategoryItem([
                    'model_id' => $newShow->id,
                    'model_type' => $newShow->getMorphClass()
                ]));
            }
        }
        return $this;
    }

    /**
     * Returns game that's been added recently.
     *
     * @param Genre|Theme|null $model
     * @param int $limit
     * @return ExploreCategory
     */
    public function recentlyUpdatedGames(Genre|Theme|null $model = null, int $limit = 10): ExploreCategory
    {
        if ($this->type === ExploreCategoryTypes::RecentlyUpdateGames) {
            $recentlyUpdatedGame = $this->game($model)
                ->recentlyUpdatedGames($limit)
                ->get(Game::TABLE_NAME . '.id');

            foreach($recentlyUpdatedGame as $recentlyUpdatedShow) {
                $this->exploreCategoryItems->add(new ExploreCategoryItem([
                    'model_id' => $recentlyUpdatedShow->id,
                    'model_type' => $recentlyUpdatedShow->getMorphClass()
                ]));
            }
        }
        return $this;
    }

    /**
     * Append the game of the current season to the category's items.
     *
     * @param Genre|Theme|null $model
     * @return ExploreCategory
     */
    public function gamesSeason(Genre|Theme|null $model = null): ExploreCategory
    {
        if ($this->type === ExploreCategoryTypes::GamesSeason) {
            $gameSeason = $this->game($model)
                ->gamesSeason()
                ->get(Game::TABLE_NAME . '.id');

            foreach($gameSeason as $game) {
                $this->exploreCategoryItems->add(new ExploreCategoryItem([
                    'model_id' => $game->id,
                    'model_type' => $game->getMorphClass()
                ]));
            }
        }
        return $this;
    }

    /**
     * Returns the current most popular game.
     *
     * @param Genre|Theme $model
     * @return ExploreCategory
     */
    public function gamesWeLove(Genre|Theme $model): ExploreCategory
    {
        if ($this->type === ExploreCategoryTypes::Games) {
            if (is_a($model, Genre::class)) {
                $randomGame = $model->games()
                    ->inRandomOrder()
                    ->limit(10)
                    ->get('id');
            } else if (is_a($model, Theme::class)) {
                $randomGame = $model->games()
                    ->inRandomOrder()
                    ->limit(10)
                    ->get('id');
            } else {
                $randomGame = Game::inRandomOrder()
                    ->limit(10)
                    ->get('id');
            }

            foreach($randomGame as $game) {
                $this->exploreCategoryItems->add(new ExploreCategoryItem([
                    'model_id' => $game->id,
                    'model_type' => $game->getMorphClass()
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
                $this->exploreCategoryItems->add(new ExploreCategoryItem([
                    'model_id' => $characterBornToday->id,
                    'model_type' => $characterBornToday->getMorphClass()
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
                $this->exploreCategoryItems->add(new ExploreCategoryItem([
                    'model_id' => $personBornToday->id,
                    'model_type' => $personBornToday->getMorphClass()
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
