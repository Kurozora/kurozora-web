<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserFavorite extends MorphPivot
{
    use HasUlids;

    // Table name
    const string TABLE_NAME = 'user_favorites';
    protected $table = self::TABLE_NAME;

    /**
     * The favorited model.
     *
     * @return MorphTo
     */
    public function favorable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The user the favorite belongs to.
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
        return $query->where('favorable_type', '=', $type);
    }
}
