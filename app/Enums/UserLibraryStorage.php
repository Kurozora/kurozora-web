<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static UserLibraryStorage HardDrive()
 * @method static UserLibraryStorage DVD()
 * @method static UserLibraryStorage RetailDVD()
 * @method static UserLibraryStorage VHS()
 * @method static UserLibraryStorage ExternalHardDrive()
 * @method static UserLibraryStorage NAS()
 * @method static UserLibraryStorage BluRay()
 * @method static UserLibraryStorage RetailManga()
 * @method static UserLibraryStorage Magazine()
 */
final class UserLibraryStorage extends Enum
{
    const int HardDrive = 0;
    const int DVD = 1;
    const int RetailDVD = 2;
    const int VHS = 3;
    const int ExternalHardDrive = 4;
    const int NAS = 5;
    const int BluRay = 6;
    const int RetailManga = 7;
    const int Magazine = 8;

    /**
     * Get the description for an enum value
     *
     * @param  mixed  $value
     * @return string
     */
    public static function getDescription(mixed $value): string
    {
        return match ((int) $value) {
            self::HardDrive => __('Hard Drive'),
            self::DVD => __('DVD / CD'),
            self::RetailDVD => __('Retail DVD'),
            self::VHS => __('VHS'),
            self::ExternalHardDrive => __('External Hard Drive'),
            self::NAS => __('NAS'),
            self::BluRay => __('Blu-ray'),
            self::RetailManga => __('Retail Manga'),
            self::Magazine => __('Magazine'),
            default => parent::getDescription((int) $value),
        };
    }
}
