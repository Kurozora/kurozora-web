<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;
use Illuminate\Support\Str;

/**
 * The canon standing of an episode, per animefillerlist.com.
 *
 * @method static EpisodeFillerKind MangaCanon()
 * @method static EpisodeFillerKind AnimeCanon()
 * @method static EpisodeFillerKind MixedCanonFiller()
 * @method static EpisodeFillerKind Filler()
 */
final class EpisodeFillerKind extends Enum
{
    // Adapted from the source manga.
    const int MangaCanon = 0;

    // Canon to the anime but original to it.
    const int AnimeCanon = 1;

    // Part canon, part filler.
    const int MixedCanonFiller = 2;

    // Non-canon, skippable without losing the story.
    const int Filler = 3;

    /**
     * Whether the kind counts as filler for the deprecated `isFiller` flag.
     *
     * @return bool
     */
    public function isFiller(): bool
    {
        return in_array($this->value, [self::Filler, self::MixedCanonFiller], true);
    }

    /**
     * Resolves a kind from an animefillerlist.com type label.
     *
     * @param string $fillerType The scraped type label.
     *
     * @return EpisodeFillerKind
     */
    public static function fromFillerType(string $fillerType): self
    {
        $normalized = Str::of($fillerType)->lower();

        return match (true) {
            // Mixed contains "filler", so it's matched before the plain kinds.
            $normalized->contains('mixed') => self::MixedCanonFiller(),
            $normalized->contains('filler') => self::Filler(),
            $normalized->contains('manga') => self::MangaCanon(),
            default => self::AnimeCanon(),
        };
    }

    /**
     * The human-readable label of one of the enum members.
     *
     * @param mixed $value
     *
     * @return string
     */
    public static function getDescription(mixed $value): string
    {
        return match ($value) {
            self::MangaCanon => __('Manga Canon'),
            self::AnimeCanon => __('Anime Canon'),
            self::MixedCanonFiller => __('Mixed Canon/Filler'),
            self::Filler => __('Filler'),
            default => parent::getDescription($value),
        };
    }
}
