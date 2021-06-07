<?php

namespace App\Models\KDashboard;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnimeProducer extends Model
{
    use HasFactory;

    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected $connection = 'pgsql';

    // Table name
    const TABLE_NAME = 'anime_producer';
    protected $table = self::TABLE_NAME;

    /**
     * The anime of the anime producer.
     *
     * @return BelongsTo
     */
    public function anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class, 'anime_id', 'id');
    }

    /**
     * The producer of the anime producer.
     *
     * @return BelongsTo
     */
    public function producer(): BelongsTo
    {
        return $this->belongsTo(ProducerMagazine::class, 'producer_id', 'id')
            ->where('type', 'anime');
    }
}
