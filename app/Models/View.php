<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class View extends KModel
{
    use HasUlids,
        SoftDeletes;

    // Table name
    const string TABLE_NAME = 'views';
    protected $table = self::TABLE_NAME;

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
