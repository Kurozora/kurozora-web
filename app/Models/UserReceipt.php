<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserReceipt extends KModel
{
    // Table name
    const string TABLE_NAME = 'user_receipts';
    protected $table = self::TABLE_NAME;

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'is_subscribed' => 'boolean',
            'will_auto_renew' => 'boolean',
            'will_price_increase' => 'boolean',
            'expiration_intent' => 'integer',
            'original_purchased_at' => 'datetime',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
            'grace_period_expires_date' => 'datetime',
        ];
    }

    /**
     * Returns the user to which the receipt belongs.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'uuid');
    }

    /**
     * Returns the store product associated with the receipt.
     */
    public function storeProduct(): BelongsTo
    {
        return $this->belongsTo(StoreProduct::class, 'product_id', 'product_id');
    }

    /**
     * Returns the transactions keyed to this receipt's original transaction id.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(UserReceiptTransaction::class, 'original_transaction_id', 'original_transaction_id');
    }
}
