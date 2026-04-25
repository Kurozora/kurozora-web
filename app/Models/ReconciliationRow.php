<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReconciliationRow extends KModel
{
    // Table name
    const string TABLE_NAME = 'reconciliation_rows';
    protected $table = self::TABLE_NAME;

    public const string STATUS_PRESENT = 'local_present';
    public const string STATUS_MISSING = 'local_missing';
    public const string STATUS_ORPHAN = 'local_orphan';

    public const string SOURCE_HISTORY = 'history';
    public const string SOURCE_NOTIFICATIONS = 'notifications';

    protected $guarded = ['id'];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    /**
     * Run that produced this row.
     */
    public function reconciliationRun(): BelongsTo
    {
        return $this->belongsTo(ReconciliationRun::class);
    }

    /**
     * User the row is attributed to, when one is known.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'uuid');
    }
}
