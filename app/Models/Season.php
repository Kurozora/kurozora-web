<?php

namespace App\Models;

use App\Traits\InteractsWithMediaExtension;
use App\Traits\Model\HasPosterImage;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Season extends KModel implements HasMedia
{
    use HasFactory,
        HasPosterImage,
        InteractsWithMedia,
        InteractsWithMediaExtension,
        Translatable;

    // Maximum relationships fetch limit
    const MAXIMUM_RELATIONSHIPS_LIMIT = 10;

    // Table name
    const TABLE_NAME = 'seasons';
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
        'first_aired' => 'date',
        'last_aired' => 'date',
    ];

    /**
     * Registers the media collections for the model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection($this->posterImageCollectionName)
            ->singleFile();
    }

    /**
     * The name of the poster image media collection.
     *
     * @var string $posterImageCollectionName
     */
    protected string $posterImageCollectionName = 'poster';

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
}
