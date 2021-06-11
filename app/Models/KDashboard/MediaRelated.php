<?php

namespace App\Models\KDashboard;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaRelated extends Model
{
    use HasFactory;

    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected $connection = 'KDashboard';

    // Table name
    const TABLE_NAME = 'media_related';
    protected $table = self::TABLE_NAME;

    /**
     * The media of the media related.
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
     * The related media of the media related.
     *
     * @return BelongsTo
     */
    public function relatedMedia(): BelongsTo
    {
        if($this->type == 'anime') {
            return $this->belongsTo(Anime::class, 'related_id', 'id');
        } else {
            return $this->belongsTo(Manga::class, 'related_id', 'id');
        }
    }

    /**
     * The related of the media related.
     *
     * @return BelongsTo
     */
    public function related(): BelongsTo
    {
        return $this->belongsTo(Related::class, 'related_type_id', 'id');
    }
}
