<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendingScrobble extends KModel
{
    // Table name
    const string TABLE_NAME = 'pending_scrobbles';
    protected $table = self::TABLE_NAME;

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'is_absolute' => 'bool',
            'watched_at' => 'datetime',
        ];
    }

    /**
     * The scrobble event payload rebuilt from the stored coordinates.
     *
     * @return array
     */
    public function toEventPayload(): array
    {
        return [
            'anime' => [
                'ids' => [
                    'mal' => $this->mal_id,
                ],
                'season' => $this->season,
                'number' => $this->number,
                'isAbsolute' => $this->is_absolute,
            ],
        ];
    }

    /**
     * The user that the PendingScrobble object belongs to.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
