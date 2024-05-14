<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Source extends KModel
{
    use HasFactory;

    // Table name
    const string TABLE_NAME = 'sources';
    protected $table = self::TABLE_NAME;

    /**
     * The anime that the source has.
     *
     * @return HasMany
     */
    public function anime(): HasMany
    {
        return $this->hasMany(Anime::class);
    }

    /**
     * The literature that the source has.
     *
     * @return HasMany
     */
    public function literatures(): HasMany
    {
        return $this->hasMany(Manga::class);
    }

    /**
     * The games that the source has.
     *
     * @return HasMany
     */
    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
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
        ];
    }
}
