<?php

namespace App\Models;

use App\Traits\InteractsWithMediaExtension;
use App\Traits\Model\HasSymbolImage;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Badge extends KModel implements HasMedia
{
    use HasSymbolImage,
        InteractsWithMedia,
        InteractsWithMediaExtension;

    // Table name
    const TABLE_NAME = 'badges';
    protected $table = self::TABLE_NAME;

    /**
     * Registers the media collections for the model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection($this->symbolImageCollectionName)
            ->singleFile();
    }

    /**
     * Returns the associated users with this badge
     *
     * @return BelongsToMany
     */
    function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, UserBadge::TABLE_NAME, 'badge_id', 'user_id')
            ->withTimestamps();
    }
}
