<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends KModel
{
    // Table name
    const string TABLE_NAME = 'user_settings';
    protected $table = self::TABLE_NAME;

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
