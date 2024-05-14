<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MediaType extends KModel
{
    use HasFactory;

    // Table name
    const string TABLE_NAME = 'media_types';
    protected $table = self::TABLE_NAME;

    /**
     * The anime that the media type has.
     *
     * @return HasMany
     */
    public function anime(): HasMany
    {
        return $this->hasMany(Anime::class);
    }

    /**
     * The literature that the media type has.
     *
     * @return HasMany
     */
    public function literatures(): HasMany
    {
        return $this->hasMany(Manga::class);
    }

    /**
     * The games that the media type has.
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
            'type' => $this->type,
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
