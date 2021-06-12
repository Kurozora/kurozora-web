<?php

namespace App\Models;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Episode extends KModel
{
    use HasFactory,
        Translatable;

    // Table name
    const TABLE_NAME = 'episodes';
    protected $table = self::TABLE_NAME;

    /**
     * Translatable attributes.
     *
     * @var array
     */
    public array $translatedAttributes = [
        'title',
        'overview',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'first_aired',
    ];

    /**
     * Returns the season this episode belongs to
     *
     * @return BelongsTo
     */
    function season(): BelongsTo
    {
        return $this->belongsTo(AnimeSeason::class);
    }
}
