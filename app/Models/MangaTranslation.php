<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MangaTranslation extends KModel
{
    use HasUlids,
        SoftDeletes;

    // Table name
    const string TABLE_NAME = 'manga_translations';
    protected $table = self::TABLE_NAME;

    /**
     * The manga the translations belongs to.
     *
     * @return BelongsTo
     */
    public function manga(): BelongsTo
    {
        return $this->belongsTo(Manga::class);
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
