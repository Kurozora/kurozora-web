<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TvRating extends Model
{
    use HasFactory;

    /**
     * The model's table name.
     *
     * @var string
     */
    const TABLE_NAME = 'tv_ratings';

    /**
     * The model's table name.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'rating',
        'description',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'full_name'
    ];

    /**
     * The full name of the rating which combines the name and description attributes.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return $this->name . ' - ' . $this->description;
    }

    /**
     * The anime belonging to the TV rating.
     *
     * @return HasMany
     */
    public function anime(): HasMany
    {
        return $this->hasMany(Anime::class);
    }
}
