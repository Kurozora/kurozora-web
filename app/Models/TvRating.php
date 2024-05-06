<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TvRating extends KModel
{
    use HasFactory,
        SoftDeletes;

    /**
     * The model's table name.
     *
     * @var string
     */
    const string TABLE_NAME = 'tv_ratings';

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
        'weight',
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

    /**
     * The mangas belonging to the TV rating.
     *
     * @return HasMany
     */
    public function manga(): HasMany
    {
        return $this->hasMany(Manga::class);
    }

    /**
     * The games belonging to the TV rating.
     *
     * @return HasMany
     */
    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }

    /**
     * The genres belonging to the TV rating.
     *
     * @return HasMany
     */
    public function genres(): HasMany
    {
        return $this->hasMany(Genre::class);
    }

    /**
     * The themes belonging to the TV rating.
     *
     * @return HasMany
     */
    public function themes(): HasMany
    {
        return $this->hasMany(Theme::class);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'weight' => $this->weight,
        ];
    }
}
