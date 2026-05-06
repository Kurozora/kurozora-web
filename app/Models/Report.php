<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Report extends KModel
{
    // Table name
    const string TABLE_NAME = 'reports';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the model that was reported.
     *
     * @return MorphTo
     */
    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Returns the user that filed this report.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
