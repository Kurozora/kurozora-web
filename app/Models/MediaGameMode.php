<?php

namespace App\Models;

use App\Enums\GameMode;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaGameMode extends MorphPivot
{
    use SoftDeletes;

    // Table name
    const string TABLE_NAME = 'media_game_modes';
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
            'game_mode' => GameMode::class,
        ];
    }

    /**
     * Returns the model in the media game mode.
     *
     * @return MorphTo
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
