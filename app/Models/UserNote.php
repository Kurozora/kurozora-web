<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserNote extends MorphPivot
{
    // Table name
    const string TABLE_NAME = 'user_notes';
    protected $table = self::TABLE_NAME;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The annotated model.
     *
     * @return MorphTo
     */
    public function noteable(): MorphTo
    {
        return $this->morphTo()
            ->withoutGlobalScopes();
    }

    /**
     * The user the note belongs to.
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
     * @param string  $type
     *
     * @return Builder
     */
    public function scopeWithType(Builder $query, string $type): Builder
    {
        return $query->where('noteable_type', '=', app($type)->getMorphClass());
    }
}
