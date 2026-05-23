<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserFollow extends MorphPivot
{
    // Table name
    const string TABLE_NAME = 'user_follows';
    protected $table = self::TABLE_NAME;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The followed model.
     *
     * @return MorphTo
     */
    public function followable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The user the follow belongs to.
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
        return $query->where('followable_type', '=', app($type)->getMorphClass());
    }
}
