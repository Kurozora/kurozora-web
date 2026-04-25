<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReconciliationUserImpact extends KModel
{
    // Table name
    const string TABLE_NAME = 'reconciliation_user_impacts';
    protected $table = self::TABLE_NAME;

    protected $guarded = ['id'];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'before_pro' => 'boolean',
            'before_plus' => 'boolean',
            'before_is_pro_flag' => 'boolean',
            'before_is_subscribed_flag' => 'boolean',
            'after_pro' => 'boolean',
            'after_plus' => 'boolean',
            'before_entitlements' => 'array',
            'after_entitlements' => 'array',
            'applied_at' => 'datetime',
            'applied_pro' => 'boolean',
            'applied_plus' => 'boolean',
        ];
    }

    /**
     * Run that produced this impact row.
     */
    public function reconciliationRun(): BelongsTo
    {
        return $this->belongsTo(ReconciliationRun::class);
    }

    /**
     * User whose entitlements are simulated.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'uuid');
    }
}
