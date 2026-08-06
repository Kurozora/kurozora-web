<?php

namespace App\Models;

use App\Enums\MediaCollection;
use App\Traits\InteractsWithMediaExtension;
use App\Traits\Model\HasSlug;
use App\Traits\Model\TvRated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
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
        HasFactory,
        HasSlug,
        SoftDeletes,
        TvRated;

    // Table name
    const string TABLE_NAME = 'themes';
    protected $table = self::TABLE_NAME;

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'is_nsfw' => 'bool',
        ];
    }

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
     * @return MorphToMany
     */
    function animes(): MorphToMany
    {
        return $this->morphedByMany(Anime::class, 'model', MediaTheme::class)
            ->withTimestamps();
    }

    /**
     * Returns the Manga with the theme
     *
     * @return MorphToMany
     */
    function mangas(): MorphToMany
    {
        return $this->morphedByMany(Manga::class, 'model', MediaTheme::class)
            ->withTimestamps();
    }

    /**
     * Returns the Game with the theme
     *
     * @return MorphToMany
     */
    function games(): MorphToMany
    {
        return $this->morphedByMany(Game::class, 'model', MediaTheme::class)
            ->withTimestamps();
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
            ->withoutGlobalScopes();
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
