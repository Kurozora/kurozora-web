<?php

namespace App\Models;

use App\Casts\AsArrayObject;
use App\Enums\StoreProductType;

class StoreProduct extends KModel
{
    // Table name
    const string TABLE_NAME = 'store_products';
    protected $table = self::TABLE_NAME;

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'type' => StoreProductType::class,
            'is_active' => 'boolean',
            'entitlements' => AsArrayObject::class,
        ];
    }
}
