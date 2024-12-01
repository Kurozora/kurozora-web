<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CharacterTranslation extends KModel
{
    use SoftDeletes;

    // Table name
    const string TABLE_NAME = 'character_translations';
    protected $table = self::TABLE_NAME;

    /**
     * The character the translations belongs to.
     *
     * @return BelongsTo
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * The language the translations belongs to.
     *
     * @return BelongsTo
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'locale', 'code');
    }
}
