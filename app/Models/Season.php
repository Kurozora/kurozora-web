<?php

namespace App\Models;

use App\Enums\MediaCollection;
use App\Scopes\TvRatingScope;
use App\Traits\InteractsWithMediaExtension;
use App\Traits\Model\HasViews;
use App\Traits\Model\TvRated;
use Astrotomic\Translatable\Translatable;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;

class Season extends KModel implements HasMedia, Sitemapable
{
    use HasFactory,
        HasViews,
        InteractsWithMedia,
        InteractsWithMediaExtension,
        SoftDeletes,
        Translatable,
        TvRated;

    // Maximum relationships fetch limit
    const int MAXIMUM_RELATIONSHIPS_LIMIT = 10;

    // Table name
    const string TABLE_NAME = 'seasons';
    protected $table = self::TABLE_NAME;

    /**
     * Translatable attributes.
     *
     * @var array
     */
    public array $translatedAttributes = [
        'title',
        'synopsis',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Season $season) {
            if (empty($season->tv_rating_id)) {
                $season->tv_rating_id = $season->anime()->withoutGlobalScopes()->first()->tv_rating_id;
            }

            if (empty($season->is_nsfw)) {
                $season->is_nsfw = $season->anime()->withoutGlobalScopes()->first()->is_nsfw;
            }
        });
    }

    /**
     * Registers the media collections for the model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollection::Poster)
            ->useFallbackUrl($this->anime?->getFirstMediaFullUrl(MediaCollection::Poster()) ?? '')
            ->singleFile();
    }

    /**
     * Returns the Anime that owns the season
     *
     * @return BelongsTo
     */
    public function anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class);
    }

    /**
     * Returns the episodes associated with the season
     *
     * @return HasMany
     */
    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class);
    }

    /**
     * Returns the episodes associated with the season
     *
     * @return HasManyThrough
     */
    public function episodesMediaStats(): HasManyThrough
    {
        return $this->hasManyThrough(MediaStat::class, Episode::class, 'season_id', 'model_id')
            ->where('model_type', '=', Episode::class);
    }

    /**
     * The season's TV rating.
     *
     * @return BelongsTo
     */
    public function tv_rating(): BelongsTo
    {
        return $this->belongsTo(TvRating::class);
    }

    /**
     * The model's translation relationship.
     *
     * @return HasOne
     */
    public function translation(): HasOne
    {
        $locale = $this->getLocaleKey();
        if ($this->useFallback()) {
            $countryFallbackLocale = $this->getFallbackLocale($locale);
            $locales = array_unique([$locale, $countryFallbackLocale, $this->getFallbackLocale()]);

            return $this->hasOne(SeasonTranslation::class)
                ->whereIn($this->getTranslationsTable().'.'.$this->getLocaleKey(), $locales);
        }

        return $this->hasOne(SeasonTranslation::class)
            ->where($this->getTranslationsTable().'.'.$this->getLocaleKey(), $locale);
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  Model|\Illuminate\Database\Eloquent\Relations\Relation  $query
     * @param  mixed  $value
     * @param  string|null  $field
     * @return Builder
     */
    public function resolveRouteBindingQuery($query, $value, $field = null): Builder
    {
        return parent::resolveRouteBindingQuery($query, $value, $field)
            ->withoutGlobalScopes([TvRatingScope::class]);
    }

    /**
     * Convert the model to its sitemap representation.
     *
     * @return Url|string|array
     */
    public function toSitemapTag(): Url|string|array
    {
        return Url::create(route('seasons.episodes', $this))
            ->setChangeFrequency('weekly')
            ->setLastModificationDate($this->updated_at);
    }
}
