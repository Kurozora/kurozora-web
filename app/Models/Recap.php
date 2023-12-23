<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recap extends KModel
{
    use HasUlids,
        SoftDeletes;

    // Table name
    const TABLE_NAME = 'recaps';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the model related to the media rating.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The items of the recap.
     *
     * @return HasMany
     */
    function recapItems(): HasMany
    {
        return $this->hasMany(RecapItem::class)
            ->orderBy('position');
    }
}
