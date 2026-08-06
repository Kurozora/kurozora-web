<?php

namespace App\Models;

use App\Enums\RatingStyle;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends KModel
{
    // Table name
    const string TABLE_NAME = 'user_settings';
    protected $table = self::TABLE_NAME;

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'rating_style' => RatingStyle::class,
        ];
    }

    /**
     * The user the settings belong to.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
