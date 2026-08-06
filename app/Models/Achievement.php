<?php

namespace App\Models;

use App\Enums\MediaCollection;
use App\Scopes\UserAchievementsScope;
use App\Traits\InteractsWithMediaExtension;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Achievement extends KModel implements HasMedia
{
    use InteractsWithMedia,
        InteractsWithMediaExtension;

    // Table name
    const string TABLE_NAME = 'achievements';
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
     * Returns the associated users with this achievement.
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, UserAchievement::class)
            ->withTimestamps();
    }

    /**
     * Returns the user-achievement pivot records for this achievement.
     *
     * @return HasMany
     */
    public function userAchievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class);
    }

    /**
     * Scope the achievements to the ones unlocked by the specified user.
     *
     * @param $query
     * @param $user
     *
     * @return void
     */
    public function scopeAchievedByUser($query, $user): void
    {
        $scope = new UserAchievementsScope($user);
        $scope->apply($query, $this);
    }
}
