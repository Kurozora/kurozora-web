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
     * Casts rules.
     *
     * @var array
     */
    protected $casts = [
        'year' => 'int'
    ];

    /**
     * The first background color of the recap.
     *
     * @return string
     */
    public function getBackgroundColor1Attribute(): string
    {
        return generate_random_color($this->year);
    }

    /**
     * The second background color of the recap.
     *
     * @return string
     */
    public function getBackgroundColor2Attribute(): string
    {
        return generate_random_color($this->year - 3);
    }

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
