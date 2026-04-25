<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsumablePurchase extends KModel
{
    // Table name
    const string TABLE_NAME = 'consumable_purchases';
    protected $table = self::TABLE_NAME;

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'purchased_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    /**
     * The user the consumable purchase belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'uuid');
    }

    /**
     * The store product associated with the purchase.
     */
    public function storeProduct(): BelongsTo
    {
        return $this->belongsTo(StoreProduct::class, 'product_id', 'product_id');
    }
}
