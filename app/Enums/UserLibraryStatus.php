<?php

namespace App\Enums;

use BenSampo\Enum\Enum;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;

/**
 * @method static UserLibraryStatus InProgress()
 * @method static UserLibraryStatus Planning()
 * @method static UserLibraryStatus Completed()
 * @method static UserLibraryStatus OnHold()
 * @method static UserLibraryStatus Dropped()
 */
final class UserLibraryStatus extends Enum
{
    const InProgress    = 0;
    const Planning      = 2;
    const Completed     = 3;
    const OnHold        = 4;
    const Dropped       = 1;

    /**
     * Make an enum instance from a given key.
     *
     * @throws InvalidEnumKeyException
     * @throws InvalidEnumMemberException
     */
    public static function fromKey(string $key): static
    {
        if (strtolower($key) == 'watching' || strtolower($key) == 'reading') {
            return UserLibraryStatus::InProgress();
        }

        if (UserLibraryStatus::hasKey($key)) {
            $enumValue = UserLibraryStatus::getValue($key);

            return new UserLibraryStatus($enumValue);
        }

        throw new InvalidEnumKeyException($key, UserLibraryStatus::class);
    }

    /**
     * Check that the enum contains a specific key.
     */
    public static function hasKey(string $key): bool
    {
        if (strtolower($key) == 'watching' || strtolower($key) == 'reading') {
            return true;
        }

        return in_array($key, UserLibraryStatus::getKeys(), true);
    }

    /**
     * Returns the description of the status
     *
     * @param int|string $value
     * @return string
     */
    public static function getAnimeDescription(mixed $value): string
    {
        return match ((int) $value) {
            self::InProgress => 'Watching',
            self::OnHold => 'On-Hold',
            default => parent::getDescription((int) $value),
        };
    }

    /**
     * Returns the description of the status
     *
     * @param int|string $value
     * @return string
     */
    public static function getMangaDescription(mixed $value): string
    {
        return match ((int) $value) {
            self::InProgress => 'Reading',
            self::OnHold => 'On-Hold',
            default => parent::getDescription((int) $value),
        };
    }

    /**
     * Get the enum as an array formatted for a select.
     *
     * @return array<array-key, string>
     */
    public static function asAnimeSelectArray(): array
    {
        $array = UserLibraryStatus::asArray();
        $selectArray = [];

        foreach ($array as $value) {
            $selectArray[$value] = UserLibraryStatus::getAnimeDescription($value);
        }

        return $selectArray;
    }

    /**
     * Get the enum as an array formatted for a select.
     *
     * @return array<array-key, string>
     */
    public static function asMangaSelectArray(): array
    {
        $array = UserLibraryStatus::asArray();
        $selectArray = [];

        foreach ($array as $value) {
            $selectArray[$value] = UserLibraryStatus::getMangaDescription($value);
        }

        return $selectArray;
    }
}
