<?php

namespace App\Models;

use App\Enums\MediaCollection;
use App\Traits\InteractsWithMediaExtension;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Badge extends KModel implements HasMedia
{
    use InteractsWithMedia,
        InteractsWithMediaExtension;

    // Table name
    const string TABLE_NAME = 'badges';
    protected $table = self::TABLE_NAME;

    /**
     * Registers the media collections for the model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollection::Symbol)
            ->singleFile();
    }

    /**
     * Returns the associated users with this badge
     *
     * @return BelongsToMany
     */
    function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, UserBadge::class, 'badge_id', 'user_id')
            ->withTimestamps();
    }
}
