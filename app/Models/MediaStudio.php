<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;

class MediaStudio extends Pivot implements Sitemapable
{
    use HasFactory,
        SoftDeletes;

    // Table name
    const TABLE_NAME = 'media_studios';
    protected $table = self::TABLE_NAME;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_licensor'   => 'boolean',
        'is_producer'   => 'boolean',
        'is_studio'     => 'boolean',
        'is_publisher'  => 'boolean',
    ];

    /**
     * Returns the anime belonging to the studio.
     *
     * @return MorphTo
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Returns the studio belonging to the anime.
     *
     * @return BelongsTo
     */
    public function studio(): BelongsTo
    {
        return $this->belongsTo(Studio::class);
    }

    /**
     * Convert the model to its sitemap representation.
     *
     * @return Url|string|array
     */
    public function toSitemapTag(): Url|string|array
    {
        return match ($this->model_type) {
            Anime::class => Url::create(route('anime.studios', $this->model))
                ->setChangeFrequency('weekly'),
            Manga::class => Url::create(route('manga.studios', $this->model))
                ->setChangeFrequency('weekly'),
            default => [],
        };
    }
}