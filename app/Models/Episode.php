<?php

namespace App\Models;

use App\Traits\InteractsWithMediaExtension;
use App\Traits\Model\HasBannerImage;
use App\Traits\Model\HasComments;
use App\Traits\Model\HasVideos;
use App\Traits\Model\HasViews;
use App\Traits\Model\TvRated;
use Astrotomic\Translatable\Translatable;
use Carbon\CarbonInterval;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;

class Episode extends KModel implements HasMedia, Sitemapable
{
    use HasBannerImage,
        HasComments,
        HasFactory,
        HasVideos,
        HasViews,
        InteractsWithMedia,
        InteractsWithMediaExtension,
        Searchable,
        SoftDeletes,
        Translatable,
        TvRated;

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
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Episode $episode) {
            if (empty($episode->tv_rating_id)) {
                $episode->tv_rating_id = $episode->anime->tv_rating_id;
            }

            if (empty($episode->is_nsfw)) {
                $episode->is_nsfw = $episode->anime->is_nsfw;
            }
        });
    }

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
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        $episode = $this->toArray();
        $episode['first_aired'] = $this->first_aired?->timestamp;
        $episode['rating_average'] = $this->stats?->rating_average ?? 0;
        $episode['created_at'] = $this->created_at?->timestamp;
        $episode['updated_at'] = $this->updated_at?->timestamp;
        return $episode;
    }

    /**
     * Returns the anime the episode belongs to.
     *
     * @return HasOneThrough
     */
    function anime(): HasOneThrough
    {
        return $this->hasOneThrough(Anime::class, Season::class, 'id', 'id', 'season_id', 'anime_id');
    }

    /**
     * Returns the season this episode belongs to.
     *
     * @return BelongsTo
     */
    function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    /**
     * The episode's TV rating.
     *
     * @return BelongsTo
     */
    public function tv_rating(): BelongsTo
    {
        return $this->belongsTo(TvRating::class);
    }

    /**
     * Returns the next episode this episode belongs to.
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
     * Get the model's videos.
     *
     * @return MorphMany
     */
    public function videos(): MorphMany
    {
        return $this->morphMany(Video::class, 'videoable')
            ->orderBy('source');
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
