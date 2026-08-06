<?php

namespace App\Models;


use App\Enums\WatchState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserWatchedEpisode extends KModel
{
    // Table name
    const string TABLE_NAME = 'user_watched_episodes';
    protected $table = self::TABLE_NAME;

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'state' => WatchState::class,
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * The attributes that mark a watch row as completed.
     *
     * @return array
     */
    public static function completedAttributes(): array
    {
        return [
            'state' => WatchState::Completed,
            'progress' => 100,
            'completed_at' => now(),
        ];
    }

    /**
     * Eloquent builder scope that limits the query to completed watch rows.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereNotNull($this->qualifyColumn('completed_at'));
    }

    /**
     * The user that the UserWatchedEpisode object belongs to.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The episode that the UserWatchedEpisode object belongs to.
     *
     * @return BelongsTo
     */
    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }
}
