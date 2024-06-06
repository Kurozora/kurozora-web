<?php

namespace App\Models;

use App\Enums\MediaCollection;
use App\Traits\InteractsWithMediaExtension;
use App\Traits\Model\HasSlug;
use App\Traits\Model\TvRated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;
use Spatie\Sluggable\SlugOptions;

class Theme extends KModel implements HasMedia, Sitemapable
{
    use InteractsWithMedia,
        InteractsWithMediaExtension,
        HasSlug,
        SoftDeletes,
        TvRated;

    // Table name
    const string TABLE_NAME = 'themes';
    protected $table = self::TABLE_NAME;

    /**
     * Get the options for generating the slug.
     *
     * @return SlugOptions
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    /**
     * Registers the media collections for the model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollection::Symbol)
            ->singleFile();
    }

    /**
     * Returns the Anime with the theme
     *
     * @return BelongsToMany
     */
    function animes(): BelongsToMany
    {
        return $this->belongsToMany(Anime::class, MediaTheme::class, 'theme_id', 'model_id')
            ->where('model_type', Anime::class)
            ->withTimestamps();
    }

    /**
     * Returns the Manga with the theme
     *
     * @return BelongsToMany
     */
    function mangas(): BelongsToMany
    {
        return $this->belongsToMany(Manga::class, MediaTheme::class, 'theme_id', 'model_id')
            ->where('model_type', Manga::class)
            ->withTimestamps();
    }

    /**
     * Returns the Game with the theme
     *
     * @return BelongsToMany
     */
    function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, MediaTheme::class, 'theme_id', 'model_id')
            ->where('model_type', Game::class)
            ->withTimestamps();
    }

    /**
     * The theme's TV rating.
     *
     * @return BelongsTo
     */
    public function tv_rating(): BelongsTo
    {
        return $this->belongsTo(TvRating::class);
    }

    /**
     * Modify the query used to retrieve models when making all of the models searchable.
     *
     * @param Builder $query
     * @return Builder
     */
    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->withoutGlobalScopes();
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'tv_rating_id' => $this->tv_rating_id,
            'name' => $this->name,
            'description' => $this->description,
            'is_nsfw' => $this->is_nsfw,
        ];
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  Model|\Illuminate\Database\Eloquent\Relations\Relation  $query
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Contracts\Database\Eloquent\Builder
     */
    public function resolveRouteBindingQuery($query, $value, $field = null): \Illuminate\Contracts\Database\Eloquent\Builder
    {
        return parent::resolveRouteBindingQuery($query, $value, $field)
            ->withoutTvRatings();
    }

    /**
     * Convert the model to its sitemap representation.
     *
     * @return Url|string|array
     */
    public function toSitemapTag(): Url|string|array
    {
        return Url::create(route('themes.details', $this))
            ->setChangeFrequency('weekly')
            ->setLastModificationDate($this->updated_at);
    }
}
