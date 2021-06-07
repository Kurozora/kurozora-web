<?php

namespace App\Models\KDashboard;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MangaCharacter extends Model
{
    use HasFactory;

    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected $connection = 'pgsql';

    // Table name
    const TABLE_NAME = 'manga_character';
    protected $table = self::TABLE_NAME;

    /**
     * The manga to which the manga character belongs.
     *
     * @return BelongsTo
     */
    public function manga(): BelongsTo
    {
        return $this->belongsTo(Manga::class, 'manga_id', 'id');
    }

    /**
     * The character to which the manga character belongs.
     *
     * @return BelongsTo
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'character_id', 'id');
    }
}
