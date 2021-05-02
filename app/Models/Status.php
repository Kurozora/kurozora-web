<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    use HasFactory;

    // Table name
    const TABLE_NAME = 'status';
    protected $table = self::TABLE_NAME;

    /**
     * The color class of the anime status.
     *
     * @return string
     */
    public function color(): string
    {
        return match ($this->name) {
            'To Be Announced' => 'amber',
            'Finished Airing' => 'red',
            'Currently Airing' => 'green',
            default => 'gray',
        };
    }

    /**
     * The anime that the media source has.
     *
     * @return HasMany
     */
    public function anime(): HasMany
    {
        return $this->hasMany(Anime::class);
    }
}
