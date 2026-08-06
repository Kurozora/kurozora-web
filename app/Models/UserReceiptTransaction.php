<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserReceiptTransaction extends KModel
{
    // Table name
    const string TABLE_NAME = 'user_receipt_transactions';
    protected $table = self::TABLE_NAME;

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'is_trial_period' => 'boolean',
            'is_in_intro_offer_period' => 'boolean',
            'is_upgraded' => 'boolean',
            'purchased_at' => 'datetime',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    /**
     * Whether the transaction can be refunded.
     */
    protected function isRefundable(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->revoked_at === null,
        );
    }

    /**
     * Returns the store product associated with the transaction.
     */
    public function storeProduct(): BelongsTo
    {
        return $this->belongsTo(StoreProduct::class, 'product_id', 'product_id');
    }

    /**
     * Returns the user receipt that owns this transaction.
     */
    public function userReceipt(): BelongsTo
    {
        return $this->belongsTo(UserReceipt::class, 'original_transaction_id', 'original_transaction_id');
    }
}
