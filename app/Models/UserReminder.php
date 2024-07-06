<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserReminder extends MorphPivot
{
    // Calendar properties
    const int|float CAL_REFRESH_INTERVAL = 60 * 24;
    const int CAL_FIRST_ALERT_MINUTES = 15;
    const int CAL_SECOND_ALERT_MINUTES = 10;
    const int|float CAL_THIRD_ALERT_DAY = 60 * 24;

    // Table name
    const string TABLE_NAME = 'user_reminders';
    protected $table = self::TABLE_NAME;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The reminded model.
     *
     * @return MorphTo
     */
    public function remindable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The user the reminders belongs to.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Eloquent builder scope that limits the query to the models with the specified type.
     *
     * @param Builder $query
     * @param string $type
     * @return Builder
     */
    public function scopeWithType(Builder $query, string $type): Builder
    {
        return $query->where('remindable_type', '=', $type);
    }
}
