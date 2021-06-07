<?php

namespace App\Models\KDashboard;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MangaMagazine extends Model
{
    use HasFactory;

    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected $connection = 'pgsql';

    // Table name
    const TABLE_NAME = 'manga_magazine';
    protected $table = self::TABLE_NAME;

    /**
     * The manga of the manga magazine.
     *
     * @return BelongsTo
     */
    public function manga(): BelongsTo
    {
        return $this->belongsTo(Manga::class, 'manga_id', 'id');
    }

    /**
     * The magazine of the manga magazine.
     *
     * @return mixed
     */
    public function magazine()
    {
        return $this->belongsTo(ProducerMagazine::class, 'magazine_id', 'id')
            ->where('type', 'manga');
    }
}
