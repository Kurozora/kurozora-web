<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recap extends KModel
{
    use SoftDeletes;

    // Table name
    const string TABLE_NAME = 'recaps';
    protected $table = self::TABLE_NAME;

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'year' => 'int',
            'mont' => 'int',
        ];
    }

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
