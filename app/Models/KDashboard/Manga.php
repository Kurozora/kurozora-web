<?php

namespace App\Models\KDashboard;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Manga extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected $connection = 'KDashboard';

    // Table name
    const TABLE_NAME = 'manga';
    protected $table = self::TABLE_NAME;

    /**
     * The genres of the manga.
     *
     * @return HasManyThrough
     */
    public function genres(): HasManyThrough
    {
        return $this->hasManyThrough(Genre::class, MediaGenre::class, 'media_id', 'id', 'id', 'genre_id')
            ->where('media_genre.type', 'manga')
            ->where('genre.type', 'manga');
    }

    /**
     * The type of the manga.
     *
     * @return BelongsTo
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class, 'manga_type_id', 'id')
            ->where('type', 'manga');
    }

    /**
     * The status of the manga.
     *
     * @return BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'manga_status_id', 'id')
            ->where('type', 'manga');
    }
}
