<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;

class Notification extends KModel
{
    use MassPrunable;

    // Table name
    const TABLE_NAME = 'notifications';
    protected $table = self::TABLE_NAME;

    /**
     * Get the prunable model query.
     *
     * @return Builder
     */
    public function prunable(): Builder
    {
        return static::whereNotNull('read_at')
            ->where('read_at', '<=', now()->subWeeks(2));
    }
}
