<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EpisodeTranslation extends KModel
{
    use HasFactory;

    // Table name
    const TABLE_NAME = 'episode_translations';
    protected $table = self::TABLE_NAME;

    /**
     * The episode the translations belongs to.
     *
     * @return BelongsTo
     */
    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
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
