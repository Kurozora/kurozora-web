<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeoutAppeal extends KModel
{
    // Table name
    const string TABLE_NAME = 'timeout_appeals';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the timeout being appealed.
     *
     * @return BelongsTo
     */
    public function timeout(): BelongsTo
    {
        return $this->belongsTo(Timeout::class);
    }
}
