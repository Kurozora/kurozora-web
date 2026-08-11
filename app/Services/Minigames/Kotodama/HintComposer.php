<?php

namespace App\Services\Minigames\Kotodama;

use App\Enums\SongType;
use App\Enums\StudioType;
use App\Models\Anime;
use App\Models\Character;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MediaSong;
use App\Models\MediaStaff;
use App\Models\Minigames\Kotodama\Word;
use App\Models\Person;
use App\Models\Song;
use App\Models\Studio;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class HintComposer
{
    /**
     * The number of credits examined when describing a subject.
     *
     * @var int
     */
    const int CREDIT_SAMPLE_LIMIT = 20;

    /**
     * The role name that describes nothing.
     *
     * @var string
     */
    const string UNKNOWN_STAFF_ROLE = 'Other';

    /**
     * The name of an unrecorded media type or source.
     *
     * @var string
     */
    const string UNKNOWN_NAME = 'Unknown';

    /**
     * The source names the adaptation facet cannot use.
     *
     * @var array
     */
    const array UNUSABLE_SOURCES = ['Unknown', 'Original', 'Music', 'Radio'];

    /**
     * The bands a number of titles is described in.
     *
     * @var int
     */
    const int BAND_SINGLE = 0;
    const int BAND_HANDFUL = 1;
    const int BAND_TEN = 2;
    const int BAND_FIFTY = 3;

    /**
     * Returns the hints describing a word's subject.
     *
     * @param Word $word
     * @param int  $limit
     *
     * @return array
     */
    public static function compose(Word $word, int $limit = 1): array
    {
        if (filled($word->hint_text)) {
            return [$word->hint_text];
        }

        $answer = (string) $word->answer;
        $hints = [];

        foreach (self::facets($word) as $facet) {
            if (count($hints) >= $limit) {
                break;
            }

            $hint = $facet();

            if (filled($hint) && !self::leaks($hint, $answer) && !in_array($hint, $hints, true)) {
                $hints[] = $hint;
            }
        }

        return $hints;
    }

    /**
     * Returns the facets a word's subject can be described by.
     *
     * @param Word $word
     *
     * @return array
     */
    protected static function facets(Word $word): array
    {
        $subject = $word->subject;

        return match (true) {
            $subject instanceof Character => self::characterFacets($word, $subject),
            $subject instanceof Person => self::personFacets($word, $subject),
            $subject instanceof Studio => self::studioFacets($word, $subject),
            $subject instanceof Anime => self::animeFacets($word, $subject),
            $subject instanceof Manga => self::mangaFacets($word, $subject),
            $subject instanceof Game => self::gameFacets($word, $subject),
            $subject instanceof Song => self::songFacets($word, $subject),
            default => [],
        };
    }

    /**
     * Returns the facets a character can be described by.
     *
     * @param Word      $word
     * @param Character $character
     *
     * @return array
     */
    protected static function characterFacets(Word $word, Character $character): array
    {
        $resolveCast = self::memoize(fn (): Collection => $character->cast()
            ->with('castRole')
            ->orderBy('cast_role_id')
            ->limit(self::CREDIT_SAMPLE_LIMIT)
            ->get());

        return [
            function () use ($word, $resolveCast): ?string {
                $name = $resolveCast()->first()?->castRole?->name;

                if (!filled($name)) {
                    return null;
                }

                $role = mb_strtolower($name);

                return match (self::variant($word, 'character.role', 2)) {
                    0 => __('Cast in an anime as the :role.', ['role' => $role]),
                    default => __('This character is the :role.', ['role' => $role]),
                };
            },
            function () use ($resolveCast): ?string {
                return match (self::band($resolveCast()->pluck('anime_id')->unique()->count())) {
                    self::BAND_SINGLE => __('This character appears in a single title.'),
                    self::BAND_HANDFUL => __('This character appears in a handful of titles.'),
                    self::BAND_TEN => __('This character appears in more than ten titles.'),
                    self::BAND_FIFTY => __('This character appears in more than fifty titles.'),
                    default => null,
                };
            },
            function () use ($word, $character): ?string {
                if (empty($character->birth_month)) {
                    return null;
                }

                $month = self::monthName((int) $character->birth_month);

                return match (self::variant($word, 'character.birthday', 2)) {
                    0 => __('This character was born in :month.', ['month' => $month]),
                    default => __('A character whose birthday falls in :month.', ['month' => $month]),
                };
            },
        ];
    }

    /**
     * Returns the facets a person can be described by.
     *
     * @param Word   $word
     * @param Person $person
     *
     * @return array
     */
    protected static function personFacets(Word $word, Person $person): array
    {
        $resolveStaffCredits = self::memoize(fn (): Collection => $person->mediaStaff()
            ->with('staffRole')
            ->limit(self::CREDIT_SAMPLE_LIMIT)
            ->get());

        return [
            function () use ($word, $resolveStaffCredits): ?string {
                $credit = self::topStaffCredit($resolveStaffCredits());

                if ($credit === null) {
                    return null;
                }

                // Role names range from job titles ('Director') to credit categories ('Story & Art').
                $role = $credit->staffRole->name;
                $isFirstPhrasing = self::variant($word, 'person.staff', 2) === 0;

                return match ($credit->model_type) {
                    Manga::class => $isFirstPhrasing
                        ? __('The name of someone whose manga credits include :role.', ['role' => $role])
                        : __('Their manga credits include :role.', ['role' => $role]),
                    Game::class => $isFirstPhrasing
                        ? __('The name of someone whose game credits include :role.', ['role' => $role])
                        : __('Their game credits include :role.', ['role' => $role]),
                    default => $isFirstPhrasing
                        ? __('The name of someone whose anime credits include :role.', ['role' => $role])
                        : __('Their anime credits include :role.', ['role' => $role]),
                };
            },
            function () use ($person): ?string {
                return match (self::band($person->animeCast()->count())) {
                    self::BAND_SINGLE => __('The name of a voice actor with a role in a single title.'),
                    self::BAND_HANDFUL => __('The name of a voice actor with roles in a handful of titles.'),
                    self::BAND_TEN => __('The name of a voice actor with roles in more than ten titles.'),
                    self::BAND_FIFTY => __('The name of a voice actor with roles in more than fifty titles.'),
                    default => null,
                };
            },
            function () use ($resolveStaffCredits): ?string {
                return match (self::band($resolveStaffCredits()->pluck('model_id')->unique()->count())) {
                    self::BAND_SINGLE => __('This person holds a credit on a single title.'),
                    self::BAND_HANDFUL => __('This person holds credits on a handful of titles.'),
                    self::BAND_TEN => __('This person holds credits on more than ten titles.'),
                    self::BAND_FIFTY => __('This person holds credits on more than fifty titles.'),
                    default => null,
                };
            },
            function () use ($word, $person): ?string {
                if ($person->birthdate === null) {
                    return null;
                }

                $month = self::monthName($person->birthdate->month);

                return match (self::variant($word, 'person.birthday', 2)) {
                    0 => __('This person was born in :month.', ['month' => $month]),
                    default => __('Someone whose birthday falls in :month.', ['month' => $month]),
                };
            },
        ];
    }

    /**
     * Returns the facets a studio can be described by.
     *
     * @param Word   $word
     * @param Studio $studio
     *
     * @return array
     */
    protected static function studioFacets(Word $word, Studio $studio): array
    {
        return [
            function () use ($word, $studio): ?string {
                $isFirstPhrasing = self::variant($word, 'studio.type', 2) === 0;

                return match (true) {
                    $studio->type?->is(StudioType::Manga) => $isFirstPhrasing
                        ? __('The name of a manga publisher.')
                        : __('This company publishes manga.'),
                    $studio->type?->is(StudioType::Game) => $isFirstPhrasing
                        ? __('The name of a game developer.')
                        : __('This company develops games.'),
                    $studio->type?->is(StudioType::Record) => $isFirstPhrasing
                        ? __('The name of a record label.')
                        : __('This company releases music.'),
                    $studio->type?->is(StudioType::Act) => $isFirstPhrasing
                        ? __('The name of a talent agency.')
                        : __('This company represents performers.'),
                    default => null,
                };
            },
            function () use ($studio): ?string {
                return match (self::band($studio->mediaStudios()->count())) {
                    self::BAND_SINGLE => __('This studio has worked on a single title.'),
                    self::BAND_HANDFUL => __('This studio has worked on a handful of titles.'),
                    self::BAND_TEN => __('This studio has worked on more than ten titles.'),
                    self::BAND_FIFTY => __('This studio has worked on more than fifty titles.'),
                    default => null,
                };
            },
            function () use ($word, $studio): ?string {
                $year = $studio->founded_at?->year;

                if ($year === null) {
                    return null;
                }

                return match (self::variant($word, 'studio.founded', 2)) {
                    0 => __('This studio was founded in :year.', ['year' => $year]),
                    default => __('A studio that has been around since :year.', ['year' => $year]),
                };
            },
            function () use ($word, $studio): ?string {
                $earliest = $studio->anime()->min(Anime::TABLE_NAME . '.started_at');

                if (blank($earliest)) {
                    return null;
                }

                $year = Carbon::parse($earliest)->year;

                return match (self::variant($word, 'studio.earliest', 2)) {
                    0 => __('This studio has been credited on titles since :year.', ['year' => $year]),
                    default => __('A studio whose earliest credit dates to :year.', ['year' => $year]),
                };
            },
        ];
    }

    /**
     * Returns the facets an anime can be described by.
     *
     * @param Word  $word
     * @param Anime $anime
     *
     * @return array
     */
    protected static function animeFacets(Word $word, Anime $anime): array
    {
        return [
            function () use ($word, $anime): ?string {
                $year = $anime->started_at?->year;

                if ($year === null) {
                    return null;
                }

                return match (self::variant($word, 'media.year', 2)) {
                    0 => __('This anime first aired in :year.', ['year' => $year]),
                    default => __('An anime that premiered in :year.', ['year' => $year]),
                };
            },
            fn (): ?string => self::mediaTypeHint($word, $anime),
            fn (): ?string => self::sourceHint($word, $anime),
        ];
    }

    /**
     * Returns the facets a manga can be described by.
     *
     * @param Word  $word
     * @param Manga $manga
     *
     * @return array
     */
    protected static function mangaFacets(Word $word, Manga $manga): array
    {
        return [
            function () use ($word, $manga): ?string {
                $year = $manga->started_at?->year ?? $manga->published_at?->year;

                if ($year === null) {
                    return null;
                }

                return match (self::variant($word, 'media.year', 2)) {
                    0 => __('This manga started running in :year.', ['year' => $year]),
                    default => __('A manga first published in :year.', ['year' => $year]),
                };
            },
            fn (): ?string => self::mediaTypeHint($word, $manga),
            fn (): ?string => self::sourceHint($word, $manga),
        ];
    }

    /**
     * Returns the facets a game can be described by.
     *
     * @param Word $word
     * @param Game $game
     *
     * @return array
     */
    protected static function gameFacets(Word $word, Game $game): array
    {
        return [
            function () use ($word, $game): ?string {
                $year = $game->started_at?->year ?? $game->published_at?->year;

                if ($year === null) {
                    return null;
                }

                return match (self::variant($word, 'media.year', 2)) {
                    0 => __('This game came out in :year.', ['year' => $year]),
                    default => __('A game released in :year.', ['year' => $year]),
                };
            },
            fn (): ?string => self::mediaTypeHint($word, $game),
            fn (): ?string => self::sourceHint($word, $game),
        ];
    }

    /**
     * Returns the facets a song can be described by.
     *
     * @param Word $word
     * @param Song $song
     *
     * @return array
     */
    protected static function songFacets(Word $word, Song $song): array
    {
        $resolveCredit = self::memoize(fn (): ?MediaSong => $song->mediaSongs()
            ->with('model')
            ->limit(self::CREDIT_SAMPLE_LIMIT)
            ->get()
            ->first());

        return [
            function () use ($word, $resolveCredit): ?string {
                $credit = $resolveCredit();

                if ($credit === null) {
                    return null;
                }

                $isFirstPhrasing = self::variant($word, 'song.type', 2) === 0;

                return match (true) {
                    $credit->type?->is(SongType::Opening) => $isFirstPhrasing
                        ? __('An opening theme.')
                        : __('A song that plays at the start of a title.'),
                    $credit->type?->is(SongType::Ending) => $isFirstPhrasing
                        ? __('An ending theme.')
                        : __('A song that plays at the end of a title.'),
                    default => $isFirstPhrasing
                        ? __('A background track.')
                        : __('A song played inside a title.'),
                };
            },
            function () use ($word, $resolveCredit): ?string {
                $model = $resolveCredit()?->model;
                $year = $model?->started_at?->year ?? $model?->published_at?->year;

                if ($year === null) {
                    return null;
                }

                return match (self::variant($word, 'song.year', 2)) {
                    0 => __('This song comes from a title released in :year.', ['year' => $year]),
                    default => __('A song from a title released in :year.', ['year' => $year]),
                };
            },
        ];
    }

    /**
     * Returns a hint naming the format a title was released in.
     *
     * @param Word             $word
     * @param Anime|Manga|Game $media
     *
     * @return string|null
     */
    protected static function mediaTypeHint(Word $word, Anime|Manga|Game $media): ?string
    {
        $name = $media->mediaType?->name;

        if (!filled($name) || $name === self::UNKNOWN_NAME) {
            return null;
        }

        return match (self::variant($word, 'media.type', 2)) {
            0 => __('Released in the :type format.', ['type' => $name]),
            default => __('A title in the :type format.', ['type' => $name]),
        };
    }

    /**
     * Returns a hint naming the source material a title was adapted from.
     *
     * @param Word             $word
     * @param Anime|Manga|Game $media
     *
     * @return string|null
     */
    protected static function sourceHint(Word $word, Anime|Manga|Game $media): ?string
    {
        $name = $media->source?->name;

        if (!filled($name) || in_array($name, self::UNUSABLE_SOURCES, true)) {
            return null;
        }

        $source = mb_strtolower($name);

        return match (self::variant($word, 'media.source', 2)) {
            0 => __('This one is adapted from a :source.', ['source' => $source]),
            default => __('A title whose source material is a :source.', ['source' => $source]),
        };
    }

    /**
     * Returns the staff credit whose role a person carries most often.
     *
     * @param Collection $credits
     *
     * @return MediaStaff|null
     */
    protected static function topStaffCredit(Collection $credits): ?MediaStaff
    {
        $named = $credits->filter(function (MediaStaff $credit) {
            return filled($credit->staffRole?->name)
                && $credit->staffRole->name !== self::UNKNOWN_STAFF_ROLE;
        });

        return $named
            ->groupBy(fn (MediaStaff $credit) => $credit->staffRole->name . '|' . $credit->model_type)
            ->sortByDesc(fn (Collection $group) => $group->count())
            ->first()
            ?->first();
    }

    /**
     * Returns the band a number of titles falls in.
     *
     * @param int $count
     *
     * @return int|null
     */
    protected static function band(int $count): ?int
    {
        return match (true) {
            $count >= 50 => self::BAND_FIFTY,
            $count >= 10 => self::BAND_TEN,
            $count >= 2 => self::BAND_HANDFUL,
            $count === 1 => self::BAND_SINGLE,
            default => null,
        };
    }

    /**
     * Returns the name of a month.
     *
     * @param int $month
     *
     * @return string
     */
    protected static function monthName(int $month): string
    {
        return now()->month($month)->translatedFormat('F');
    }

    /**
     * Wraps a resolver so it runs at most once.
     *
     * @param callable $resolver
     *
     * @return callable
     */
    protected static function memoize(callable $resolver): callable
    {
        $resolved = false;
        $value = null;

        return function () use ($resolver, &$resolved, &$value) {
            if (!$resolved) {
                $resolved = true;
                $value = $resolver();
            }

            return $value;
        };
    }

    /**
     * Returns which of a facet's phrasings the word uses.
     *
     * @param Word   $word
     * @param string $salt
     * @param int    $count
     *
     * @return int
     */
    protected static function variant(Word $word, string $salt, int $count): int
    {
        return crc32($salt . ':' . $word->id) % $count;
    }

    /**
     * Whether the given text gives the answer away.
     *
     * @param string $text
     * @param string $answer
     *
     * @return bool
     */
    protected static function leaks(string $text, string $answer): bool
    {
        $normalize = static fn (string $value): string => (string) preg_replace(
            '/[^\p{L}\p{N}]+/u',
            '',
            mb_strtolower($value)
        );

        $needle = $normalize($answer);

        return $needle !== '' && str_contains($normalize($text), $needle);
    }
}
