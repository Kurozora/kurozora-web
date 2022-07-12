<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static SearchType Characters()
 * @method static SearchType Games()
 * @method static SearchType Literature()
 * @method static SearchType People()
 * @method static SearchType Shows()
 * @method static SearchType Songs()
 * @method static SearchType Studios()
 * @method static SearchType Users()
 */
final class SearchType extends Enum
{
    const Characters = 'characters';
    const Games = 'games';
    const Literature = 'literature';
    const People = 'people';
    const Shows = 'shows';
    const Songs = 'songs';
    const Studios = 'studios';
    const Users = 'users';

    /**
     * Get all or a custom set of the enum values.
     *
     * @param string|string[]|null $keys
     *
     * @return array
     */
    public static function getWebValues(SearchScope|array|string|null $keys = null): array
    {
        return match($keys->value ?? $keys) {
            SearchScope::Library => ['shows'],
            SearchScope::Kurozora => [
                'shows',
                'characters',
                'people',
                'studios',
                'users',
            ],
            default => parent::getValues($keys)
        };
    }

    /**
     * Get the enum as an array formatted for a web version select.
     *
     * @param SearchScope|string|null $scope
     * @return array
     */
    public static function asWebSelectArray(SearchScope|string|null $scope = null): array
    {
        $selectArray = [];
        $selectArray['shows'] = 'Anime';
        if ($scope != SearchScope::Library || ($scope instanceof SearchScope && $scope->value != SearchScope::Library)) {
            $selectArray['characters'] = 'Characters';
            $selectArray['people'] = 'People';
            $selectArray['studios'] = 'Studios';
            $selectArray['users'] = 'Users';
        }
        return $selectArray;
    }
}
