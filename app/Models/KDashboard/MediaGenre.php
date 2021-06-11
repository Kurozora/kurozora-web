<?php

namespace App\Models\KDashboard;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaGenre extends Model
{
    use HasFactory;

    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected $connection = 'KDashboard';

    const TABLE_NAME = 'media_genre';
    protected $table = self::TABLE_NAME;

    /**
     * The media to which the media genre belongs.
     *
     * @return BelongsTo
     */
    public function media(): BelongsTo
    {
        if($this->type == 'anime') {
            return $this->belongsTo(Anime::class, 'media_id', 'id');
        } else {
            return $this->belongsTo(Manga::class, 'media_id', 'id');
        }
    }

    /**
     * The genre to which the media genre belongs.
     *
     * @return BelongsTo
     */
    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class, 'genre_id', 'id')
            ->where('type', $this->type);
    }
}
