<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;

class View extends KModel
{
    // Table name
    const string TABLE_NAME = 'views';
    protected $table = self::TABLE_NAME;

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
