<?php

namespace App\Models;

use Carbon\Month;
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
            'month' => 'int',
        ];
    }

    /**
     * The first background color of the recap.
     *
     * @return string
     */
    public function getBackgroundColor1Attribute(): string
    {
        return generate_spectrum_color($this->year);
    }

    /**
     * The second background color of the recap.
     *
     * Recaps are listed newest first, so the second color is the year that follows
     * this one in the list, letting neighbouring recaps meet on a shared color.
     *
     * @return string
     */
    public function getBackgroundColor2Attribute(): string
    {
        return generate_spectrum_color($this->year - 1);
    }

    /**
     * The tint of the fringe drawn above the core of the recap’s crescent.
     *
     * @return string
     */
    public function getCoolFringeColorAttribute(): string
    {
        return lit_variant_color($this->background_color1, -21.6);
    }

    /**
     * The tint of the fringe drawn below the core of the recap’s crescent.
     *
     * @return string
     */
    public function getWarmFringeColorAttribute(): string
    {
        return lit_variant_color($this->background_color2, 21.6);
    }

    /**
     * Whether the recap’s colors are bright enough to need dark content on top of them.
     *
     * @return bool
     */
    public function getIsLightAttribute(): bool
    {
        return color_is_light($this->background_color1) && color_is_light($this->background_color2);
    }

    /**
     * The recap’s month’s name.
     *
     * @return string
     */
    public function getMonthNameAttribute(): string
    {
        return Month::from($this->month)->name;
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
