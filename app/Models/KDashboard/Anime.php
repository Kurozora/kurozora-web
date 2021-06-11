<?php

namespace App\Models\KDashboard;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Anime extends Model
{
    use HasFactory;

    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected $connection = 'KDashboard';

    // Table name
    const TABLE_NAME = 'anime';
    protected $table = self::TABLE_NAME;

    /**
     * The genres of the anime.
     *
     * @return HasManyThrough
     */
    public function genres(): HasManyThrough
    {
        return $this->hasManyThrough(Genre::class, MediaGenre::class, 'media_id', 'id', 'id', 'genre_id')
            ->where('media_genre.type', 'anime')
            ->where('genre.type', 'anime');
    }

    /**
     * The type of the anime.
     *
     * @return BelongsTo
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class, 'anime_type_id', 'id');
    }

    /**
     * The status of the anime.
     *
     * @return BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'anime_status_id', 'id')
            ->where('type', 'anime');
    }

    /**
     * The source of the anime.
     *
     * @return BelongsTo
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class, 'anime_source_id', 'id');
    }

    /**
     * The rating of the anime.
     *
     * @return BelongsTo
     */
    public function rating(): BelongsTo
    {
        return $this->belongsTo(Rating::class, 'anime_rating_id', 'id');
    }
}
