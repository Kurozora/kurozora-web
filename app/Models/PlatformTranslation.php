<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlatformTranslation extends KModel
{
    use SoftDeletes;

    // Table name
    const string TABLE_NAME = 'platform_translations';
    protected $table = self::TABLE_NAME;

    /**
     * The platform the translations belongs to.
     *
     * @return BelongsTo
     */
    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
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
