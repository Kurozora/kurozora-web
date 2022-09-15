<?php

namespace App\Models;

use App\Traits\Model\HasUuid;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class View extends KModel
{
    use HasUuid,
        SoftDeletes;

    // Table name
    const TABLE_NAME = 'views';
    protected $table = self::TABLE_NAME;

    /**
     * The "type" of the primary key ID.
     *
     * @var string $keyType
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool $incrementing
     */
    public $incrementing = false;

    /**
     * Casts rules.
     *
     * @var array
     */
    protected $casts = [
        'viewed_at' => 'datetime'
    ];

    /**
     * Get the view entity that the view belongs to.
     *
     * @return MorphTo
     */
    public function viewable(): MorphTo
    {
        return $this->morphTo();
    }
}
