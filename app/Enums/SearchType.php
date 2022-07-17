<?php

namespace App\Enums;

use App\Models\Anime;
use App\Models\Character;
use App\Models\Episode;
use App\Models\Person;
use App\Models\Song;
use App\Models\Studio;
use App\Models\User;
use BenSampo\Enum\Enum;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;

/**
 * @method static SearchType Characters()
 * @method static SearchType Episodes()
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
    const Episodes = 'episodes';
    const Games = 'games';
    const Literature = 'literature';
    const People = 'people';
    const Shows = 'shows';
    const Songs = 'songs';
    const Studios = 'studios';
    const Users = 'users';

    /**
     * Make an enum instance from a given model.
     *
     * @param string $model
     * @return static
     *
     * @throws InvalidEnumKeyException
     */
    public static function fromModel(string $model): self
    {
        return match ($model) {
            Anime::class => SearchType::Shows(),
            Character::class => SearchType::Characters(),
            Episode::class => SearchType::Episodes(),
            Person::class => SearchType::People(),
            Song::class => SearchType::Songs(),
            Studio::class => SearchType::Studios(),
            User::class => SearchType::Users(),
            default => throw new InvalidEnumKeyException($model, SearchType::class)
        };
    }

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
                'episodes',
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
            $selectArray['episodes'] = 'Episodes';
            $selectArray['characters'] = 'Characters';
            $selectArray['people'] = 'People';
            $selectArray['studios'] = 'Studios';
            $selectArray['users'] = 'Users';
        }
        return $selectArray;
    }
}
