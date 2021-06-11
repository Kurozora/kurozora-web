<?php

namespace App\Models\KDashboard;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatsHistory extends Model
{
    use HasFactory;

    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected $connection = 'KDashboard';

    // Table name
    const TABLE_NAME = 'stats_history';
    protected $table = self::TABLE_NAME;

    /**
     * The media of the stats history.
     *
     * @return BelongsTo
     */
    public function media(): BelongsTo
    {
        if($this->type == 'anime') {
            return $this->belongsTo(Anime::class, 'media_id', 'id');
        } else if($this->type == 'manga') {
            return $this->belongsTo(Manga::class, 'media_id', 'id');
        } else if($this->type == 'character') {
            return $this->belongsTo(Character::class, 'media_id', 'id');
        } else {
            return $this->belongsTo(People::class, 'media_id', 'id');
        }
    }
}
