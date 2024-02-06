<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserReceipt extends KModel
{
    // Table name
    const string TABLE_NAME = 'user_receipts';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the user to which the receipt belongs.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'uuid');
    }
}
