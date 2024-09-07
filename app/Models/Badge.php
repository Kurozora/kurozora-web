<?php

namespace App\Models;

use App\Enums\MediaCollection;
use App\Scopes\UserAchievementsScope;
use App\Traits\InteractsWithMediaExtension;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'is_unlockable' => 'bool',
        ];
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
     * Returns the associated users with this badge
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, UserBadge::class)
            ->withTimestamps();
    }

    /**
     * Returns the associated user_badges
     *
     * @return HasMany
     */
    public function user_badges(): HasMany
    {
        return $this->hasMany(UserBadge::class);
    }

    /**
     * Scope the badges to the ones achieved by the specified user.
     *
     * @param $query
     * @param $user
     *
     * @return void
     */
    public function scopeAchievedUserBadges($query, $user): void
    {
        $bornToday = new UserAchievementsScope($user);
        $bornToday->apply($query, $this);
    }
}
