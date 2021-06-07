<?php

namespace App\Models\KDashboard;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeopleManga extends Model
{
    use HasFactory;

    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected $connection = 'pgsql';

    // Table name
    const TABLE_NAME = 'people_manga';
    protected $table = self::TABLE_NAME;

    /**
     * The person of the manga.
     *
     * @return BelongsTo
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(People::class, 'people_id', 'id');
    }

    /**
     * The manga of the manga.
     *
     * @return BelongsTo
     */
    public function manga(): BelongsTo
    {
        return $this->belongsTo(Manga::class, 'manga_id', 'id');
    }

    /**
     * The position of the manga.
     *
     * @return BelongsTo
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id', 'id');
    }
}
