<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class ReconciliationRun extends KModel
{
    // Table name
    const string TABLE_NAME = 'reconciliation_runs';
    protected $table = self::TABLE_NAME;

    protected $guarded = ['id'];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'since' => 'datetime',
            'until' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Diff rows emitted during the run.
     */
    public function rows(): HasMany
    {
        return $this->hasMany(ReconciliationRow::class);
    }

    /**
     * Per-user before/after entitlement simulations for the run.
     */
    public function userImpacts(): HasMany
    {
        return $this->hasMany(ReconciliationUserImpact::class);
    }
}
