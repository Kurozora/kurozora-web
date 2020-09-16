<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static StoreProductType Consumable()
 * @method static StoreProductType NonConsumable()
 * @method static StoreProductType NonRenewingSubscription()
 * @method static StoreProductType AutoRenewingSubscription()
 */
final class StoreProductType extends Enum
{
    const Consumable                = 0;
    const NonConsumable             = 1;
    const NonRenewingSubscription   = 2;
    const AutoRenewingSubscription  = 3;
}
