<?php

namespace App\Models\KDashboard;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnimeStaff extends Model
{
    use HasFactory;

    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected $connection = 'KDashboard';

    // Table name
    const TABLE_NAME = 'anime_staff';
    protected $table = self::TABLE_NAME;

    /**
     * The anime to which the anime staff belongs.
     *
     * @return BelongsTo
     */
    public function anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class, 'anime_id', 'id');
    }

    /**
     * The person to which the anime staff belongs.
     *
     * @return BelongsTo
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(People::class, 'people_id', 'id');
    }

    /**
     * The position to which the anime staff belongs.
     *
     * @return BelongsTo
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id', 'id');
    }
}
