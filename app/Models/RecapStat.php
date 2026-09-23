<?php

namespace App\Models;

use App\Enums\RecapStatType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RecapStat extends KModel
{
    // Table name
    const string TABLE_NAME = 'recap_stats';
    protected $table = self::TABLE_NAME;

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'year' => 'int',
            'month' => 'int',
            'stat' => RecapStatType::class,
            'value' => 'int',
            'occurred_at' => 'date',
        ];
    }

    /**
     * The user the stat belongs to.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The model the stat refers to.
     *
     * @return MorphTo
     */
    public function model(): MorphTo
    {
        return $this->morphTo()
            ->withoutGlobalScopes();
    }
}
