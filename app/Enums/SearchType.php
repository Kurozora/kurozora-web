<?php declare(strict_types=1);

namespace App\Enums;

use App\Models\Anime;
use App\Models\Character;
use App\Models\Episode;
use App\Models\Game;
use App\Models\Manga;
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
 * @method static SearchType Literatures()
 * @method static SearchType People()
 * @method static SearchType Shows()
 * @method static SearchType Songs()
 * @method static SearchType Studios()
 * @method static SearchType Users()
 */
final class SearchType extends Enum
{
    const string Characters = 'characters';
    const string Episodes = 'episodes';
    const string Games = 'games';
    const string Literatures = 'literatures';
    const string People = 'people';
    const string Shows = 'shows';
    const string Songs = 'songs';
    const string Studios = 'studios';
    const string Users = 'users';

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
            Manga::class => SearchType::Literatures(),
            Game::class => SearchType::Games(),
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
            SearchScope::Library => [
                SearchType::Shows,
                SearchType::Literatures,
                SearchType::Games,
            ],
            SearchScope::Kurozora => [
                SearchType::Shows,
                SearchType::Literatures,
                SearchType::Games,
                SearchType::Episodes,
                SearchType::Characters,
                SearchType::People,
                SearchType::Songs,
                SearchType::Studios,
                SearchType::Users,
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
        $selectArray = [
            SearchType::Shows => __('Anime'),
            SearchType::Literatures => __('Manga'),
            SearchType::Games => __('Games'),
        ];

        if ($scope != SearchScope::Library || ($scope instanceof SearchScope && $scope->value != SearchScope::Library)) {
            $selectArray[SearchType::Episodes] = __('Episodes');
            $selectArray[SearchType::Characters] = __('Characters');
            $selectArray[SearchType::People] = __('People');
            $selectArray[SearchType::Songs] = __('Songs');
            $selectArray[SearchType::Studios] = __('Studios');
            $selectArray[SearchType::Users] = __('Users');
        }

        return $selectArray;
    }
}
