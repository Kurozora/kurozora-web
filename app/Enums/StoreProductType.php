<?php declare(strict_types=1);

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
    const int Consumable                = 0;
    const int NonConsumable             = 1;
    const int NonRenewingSubscription   = 2;
    const int AutoRenewingSubscription  = 3;
}
