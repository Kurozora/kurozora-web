<?php

namespace App\Services\Minigames\Kotodama;

use App\Enums\Minigames\Kotodama\Difficulty;
use App\Models\Anime;
use App\Models\Character;
use App\Models\Game;
use App\Models\Manga;
use App\Models\Minigames\Kotodama\Word;
use App\Models\Person;
use App\Models\Song;
use App\Models\Studio;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class WordGenerator
{
    /**
     * The catalog sources mined for answers.
     *
     * @var array
     */
    const array SOURCES = [
        Character::class => ['column' => 'slug', 'whole' => false, 'gated' => true],
        Studio::class => ['column' => 'slug', 'whole' => false, 'gated' => true],
        Person::class => ['column' => 'slug', 'whole' => true, 'gated' => false],
        Anime::class => ['column' => 'original_title', 'whole' => true, 'gated' => false],
        Manga::class => ['column' => 'original_title', 'whole' => true, 'gated' => false],
        Game::class => ['column' => 'original_title', 'whole' => true, 'gated' => false],
        Song::class => ['column' => 'original_title', 'whole' => true, 'gated' => false],
    ];

    /**
     * Sources whose NSFW flag an answer inherits.
     *
     * @var array
     */
    const array NSFW_AWARE_SOURCES = [Studio::class, Anime::class, Manga::class, Game::class];

    const int EASY_RANK_CEILING = 500;
    const int NORMAL_RANK_CEILING = 2000;

    const int CHUNK_SIZE = 500;

    const float FILLER_FREQUENCY_RATIO = 0.01;
    const int FILLER_FREQUENCY_FLOOR = 10;
    const int SAMPLE_LIMIT = 25;

    /**
     * Mine every catalog source for new answers.
     *
     * @param int      $rankLimit
     * @param int|null $limit
     * @param bool     $dryRun
     * @param array    $samples
     *
     * @return int
     */
    public static function generate(int $rankLimit, ?int $limit = null, bool $dryRun = false, array &$samples = []): int
    {
        $known = array_fill_keys(Word::pluck('answer')->all(), true);

        $generated = 0;

        foreach (self::SOURCES as $source => $descriptor) {
            self::mine($source, $descriptor, $rankLimit, $limit, $dryRun, $known, $generated, $samples);
        }

        return $generated;
    }

    /**
     * Mine one catalog source for new answers.
     *
     * @param string   $source
     * @param array    $descriptor
     * @param int      $rankLimit
     * @param int|null $limit
     * @param bool     $dryRun
     * @param array    $known
     * @param int      $generated
     * @param array    $samples
     *
     * @return void
     */
    protected static function mine(string $source, array $descriptor, int $rankLimit, ?int $limit, bool $dryRun, array &$known, int &$generated, array &$samples): void
    {
        $column = $descriptor['column'];
        $whole = $descriptor['whole'];
        $gated = $descriptor['gated'];

        $filler = $whole ? [] : self::fillerTokens($source, $rankLimit, $column, $gated);

        $columns = ['id', $column, 'rank_total'];

        if (in_array($source, self::NSFW_AWARE_SOURCES, true)) {
            $columns[] = 'is_nsfw';
        }

        self::candidates($source, $rankLimit, $gated)
            ->select($columns)
            ->chunkById(self::CHUNK_SIZE, function (Collection $subjects) use (
                $column,
                $whole,
                $filler,
                $limit,
                $dryRun,
                &$known,
                &$generated,
                &$samples
            ) {
                $pending = [];
                $now = Carbon::now();

                foreach ($subjects as $subject) {
                    if ($limit !== null && $generated >= $limit) {
                        break;
                    }

                    $value = (string) $subject->{$column};

                    $answer = $whole
                        ? AnswerTokenizer::whole($value)
                        : self::pickAnswer($value, $filler);

                    if ($answer === null || isset($known[$answer])) {
                        continue;
                    }

                    $known[$answer] = true;
                    $generated++;

                    if (count($samples) < self::SAMPLE_LIMIT) {
                        $samples[$answer] = $value;
                    }

                    $pending[] = [
                        'answer' => $answer,
                        'difficulty' => self::difficultyFor((int) $subject->rank_total)->value,
                        'subject_type' => $subject->getMorphClass(),
                        'subject_id' => $subject->id,
                        'hint_text' => null,
                        'is_nsfw' => (bool) ($subject->is_nsfw ?? false),
                        'is_active' => true,
                        'released_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if (!$dryRun && $pending !== []) {
                    Word::insertOrIgnore($pending);
                }

                return $limit === null || $generated < $limit;
            });
    }

    /**
     * The tokens a source repeats too widely to name anything.
     *
     * @param string $source
     * @param int    $rankLimit
     * @param string $column
     * @param bool   $gated
     *
     * @return array
     */
    protected static function fillerTokens(string $source, int $rankLimit, string $column, bool $gated): array
    {
        $frequency = [];
        $subjectCount = 0;

        self::candidates($source, $rankLimit, $gated)
            ->select(['id', $column])
            ->chunkById(self::CHUNK_SIZE, function (Collection $subjects) use ($column, &$frequency, &$subjectCount) {
                foreach ($subjects as $subject) {
                    $subjectCount++;

                    foreach (AnswerTokenizer::extract((string) $subject->{$column}) as $token) {
                        $frequency[$token] = ($frequency[$token] ?? 0) + 1;
                    }
                }
            });

        $ceiling = max(self::FILLER_FREQUENCY_FLOOR, (int) ($subjectCount * self::FILLER_FREQUENCY_RATIO));

        return array_filter($frequency, fn (int $count) => $count > $ceiling);
    }

    /**
     * The token that identifies a name.
     *
     * @param string $subject
     * @param array  $filler
     *
     * @return string|null
     */
    protected static function pickAnswer(string $subject, array $filler): ?string
    {
        foreach (AnswerTokenizer::extract($subject) as $token) {
            if (!isset($filler[$token])) {
                return $token;
            }
        }

        return null;
    }

    /**
     * The subjects a source offers.
     *
     * @param string $source
     * @param int    $rankLimit
     * @param bool   $gated
     *
     * @return Builder
     */
    protected static function candidates(string $source, int $rankLimit, bool $gated): Builder
    {
        return $source::withoutGlobalScopes()
            ->whereNull('deleted_at')
            ->when($gated, fn (Builder $query) => $query
                ->where('rank_total', '>', 0)
                ->where('rank_total', '<=', $rankLimit));
    }

    /**
     * The difficulty a subject's global rank implies.
     *
     * @param int $rankTotal
     *
     * @return Difficulty
     */
    protected static function difficultyFor(int $rankTotal): Difficulty
    {
        return match (true) {
            $rankTotal <= 0 => Difficulty::Hard(),
            $rankTotal <= self::EASY_RANK_CEILING => Difficulty::Easy(),
            $rankTotal <= self::NORMAL_RANK_CEILING => Difficulty::Normal(),
            default => Difficulty::Hard(),
        };
    }
}
