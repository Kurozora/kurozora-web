<?php

namespace App\Models;

use App\Enums\PlayerPerspective;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaPlayerPerspective extends MorphPivot
{
    use SoftDeletes;

    // Table name
    const string TABLE_NAME = 'media_player_perspectives';
    protected $table = self::TABLE_NAME;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'player_perspective' => PlayerPerspective::class,
        ];
    }

    /**
     * Returns the model in the media player perspective.
     *
     * @return MorphTo
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
