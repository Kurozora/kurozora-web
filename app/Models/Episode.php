<?php

namespace App\Models;

use App\Traits\InteractsWithMediaExtension;
use App\Traits\Model\HasBannerImage;
use Astrotomic\Translatable\Translatable;
use Carbon\CarbonInterval;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Episode extends KModel implements HasMedia
{
    use HasBannerImage,
        HasFactory,
        InteractsWithMedia,
        InteractsWithMediaExtension,
        Translatable;

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
        'first_aired' => 'date',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'banner_image',
        'banner_image_url',
        'duration_string',
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
     * Returns the season this episode belongs to
     *
     * @return BelongsTo
     */
    function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }
}
