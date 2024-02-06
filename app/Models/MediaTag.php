<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaTag extends KModel
{
    use HasUlids,
        SoftDeletes;

    // Table name
    const string TABLE_NAME = 'media_tags';
    protected $table = self::TABLE_NAME;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool $incrementing
     */
    public $incrementing = false;

    /**
     * The tag relationship of the media tag.
     *
     * @return BelongsTo
     */
    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }

    /**
     * * Returns the model in the media tag.
     *
     * @return MorphTo
     */
    public function taggable(): MorphTo
    {
        return $this->morphTo();
    }
}
