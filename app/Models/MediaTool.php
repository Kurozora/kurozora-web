<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaTool extends MorphPivot
{
    use SoftDeletes;

    // Table name
    const string TABLE_NAME = 'media_tools';
    protected $table = self::TABLE_NAME;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Returns the model in the media tool.
     *
     * @return MorphTo
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The tool belonging to the media tool.
     *
     * @return BelongsTo
     */
    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }
}
