<?php

namespace App\Models;

use App\Traits\InteractsWithMediaExtension;
use App\Traits\Model\HasBannerImage;
use Astrotomic\Translatable\Translatable;
use Carbon\CarbonInterval;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Cache;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;

class Episode extends KModel implements HasMedia, Sitemapable
{
    use HasBannerImage,
        HasFactory,
        InteractsWithMedia,
        InteractsWithMediaExtension,
        Searchable,
        Translatable;

    // How long to cache certain responses
    const CACHE_KEY_STATS_SECONDS = 120 * 60;

    // Minimum ratings required to calculate average
    const MINIMUM_RATINGS_REQUIRED = 1;

    // Table name
    const TABLE_NAME = 'episodes';
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
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'first_aired' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
//        'banner_image',
//        'banner_image_url',
//        'duration_string',
    ];

    /**
     * Registers the media collections for the model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection($this->bannerImageCollectionName)
            ->singleFile();
    }

    /**
     * Ge the episode's duration as a humanly readable string.
     *
     * @return string
     * @throws Exception
     */
    public function getDurationStringAttribute(): string
    {
        $runtime = $this->duration ?? 0;
        return CarbonInterval::seconds($runtime)->cascade()->forHumans();
    }

    /**
     * Get the name of the index associated with the model.
     *
     * @return string
     */
    public function searchableAs(): string
    {
        return 'episodes_index';
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        $episode = $this->toArray();
        $searchableArray = [
            'first_aired' => $this->first_aired?->timestamp,
            'rating_average' => $this->stats?->rating_average ?? 0,
        ];
        return array_merge($episode, $searchableArray);
    }

    /**
     * Returns the season this episode belongs to
     *
     * @return BelongsTo
     */
    function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    /**
     * Returns the next episode this episode belongs to
     *
     * @return BelongsTo
     */
    function next_episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }

    /**
     * Returns the previous episode this episode belongs to
     *
     * @return BelongsTo
     */
    function previous_episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }

    /**
     * Returns the media relations.
     *
     * @return mixed
     */
    public function getStats(): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'episode.stats', 'id' => $this->id]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_STATS_SECONDS, function () {
            return $this->stats;
        });
    }

    /**
     * The media stats of this episode.
     *
     * @return HasOne
     */
    public function stats(): HasOne
    {
        return $this->hasOne(MediaStat::class, 'model_id')
            ->where('model_type', Episode::class);
    }

    /**
     * The media rating relationship of the episode.
     *
     * @return MorphMany
     */
    function ratings(): MorphMany
    {
        return $this->morphMany(MediaRating::class, 'model')
            ->where('model_type', Episode::class);
    }

    /**
     * Convert the model to its sitemap representation.
     *
     * @return Url|string|array
     */
    public function toSitemapTag(): Url|string|array
    {
        return Url::create(route('episodes.details', $this))
            ->setChangeFrequency('weekly');
    }
}
