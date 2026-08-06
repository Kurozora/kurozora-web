<?php

namespace App\Models\Minigames\Kotodama;

use App\Enums\MediaCollection;
use App\Enums\Minigames\Kotodama\Difficulty;
use App\Models\Anime;
use App\Models\Character;
use App\Models\Game;
use App\Models\KModel;
use App\Models\Manga;
use App\Models\Media;
use App\Models\Person;
use App\Models\Song;
use App\Models\Studio;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

class Word extends KModel
{
    // Table name
    const string TABLE_NAME = 'kotodama_words';
    protected $table = self::TABLE_NAME;

    const int LENGTH = 5;

    /**
     * The hint image URL.
     *
     * @var string|null|false
     */
    protected string|null|false $hintImageUrl = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'difficulty'  => Difficulty::class,
            'is_nsfw'     => 'bool',
            'is_active'   => 'bool',
            'released_at' => 'datetime',
        ];
    }

    /**
     * Returns the subject relationship.
     *
     * @return MorphTo
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Returns the daily puzzle this word is scheduled for.
     *
     * @return HasOne
     */
    public function dailyPuzzle(): HasOne
    {
        return $this->hasOne(DailyPuzzle::class, 'word_id');
    }

    /**
     * Returns the hint shown once enough guesses are spent.
     *
     * @return string|null
     */
    public function getHint(): ?string
    {
        if (filled($this->hint_text)) {
            return $this->hint_text;
        }

        $subject = $this->subject;
        $year = $subject?->started_at?->year ?? $subject?->published_at?->year;

        return match (true) {
            $subject instanceof Character               => __('The name of an anime character.'),
            $subject instanceof Person                  => __('The name of someone who works in the anime industry.'),
            $subject instanceof Studio                  => __('The name of an animation studio.'),
            $subject instanceof Anime && $year !== null => __('The complete title of an anime that premiered in :year.', ['year' => $year]),
            $subject instanceof Anime                   => __('The complete title of an anime.'),
            $subject instanceof Manga && $year !== null => __('The complete title of a manga first published in :year.', ['year' => $year]),
            $subject instanceof Manga                   => __('The complete title of a manga.'),
            $subject instanceof Game && $year !== null  => __('The complete title of a game released in :year.', ['year' => $year]),
            $subject instanceof Game                    => __('The complete title of a game.'),
            $subject instanceof Song                    => __('The complete title of an anime song.'),
            default                                     => null,
        };
    }

    /**
     * Returns the details URL for the linked subject.
     *
     * @return string|null
     */
    public function getSubjectUrl(): ?string
    {
        return match (true) {
            $this->subject instanceof Anime => route('anime.details', $this->subject),
            $this->subject instanceof Manga => route('manga.details', $this->subject),
            $this->subject instanceof Game => route('games.details', $this->subject),
            $this->subject instanceof Character => route('characters.details', $this->subject),
            $this->subject instanceof Person => route('people.details', $this->subject),
            $this->subject instanceof Studio => route('studios.details', $this->subject),
            $this->subject instanceof Song => route('songs.details', $this->subject),
            default => null,
        };
    }

    /**
     * Returns the image of the linked subject.
     *
     * @return Media|null
     */
    public function getHintImage(): ?Media
    {
        $subject = $this->subject;

        if (!$subject || !method_exists($subject, 'getFirstMedia')) {
            return null;
        }

        return $subject->getFirstMedia($this->getHintImageCollection());
    }

    /**
     * Returns an image URL for the linked subject.
     *
     * @return string|null
     */
    public function getHintImageUrl(): ?string
    {
        if ($this->hintImageUrl !== false) {
            return $this->hintImageUrl;
        }

        $subject = $this->subject;

        if (!$subject || !method_exists($subject, 'getFirstMediaFullUrl')) {
            return $this->hintImageUrl = null;
        }

        return $this->hintImageUrl = $subject->getFirstMediaFullUrl($this->getHintImageCollection()) ?: null;
    }

    /**
     * Returns the kind of the linked subject.
     *
     * @return string|null
     */
    public function getSubjectKind(): ?string
    {
        return match (true) {
            $this->subject instanceof Anime => 'shows',
            $this->subject instanceof Manga => 'literatures',
            $this->subject instanceof Game => 'games',
            $this->subject instanceof Character => 'characters',
            $this->subject instanceof Person => 'people',
            $this->subject instanceof Studio => 'studios',
            $this->subject instanceof Song => 'songs',
            default => null,
        };
    }

    /**
     * Returns the media collection holding the subject's image.
     *
     * @return MediaCollection
     */
    public function getHintImageCollection(): MediaCollection
    {
        return match (true) {
            $this->subject instanceof Character,
            $this->subject instanceof Person,
            $this->subject instanceof Studio => MediaCollection::Profile(),
            $this->subject instanceof Song => MediaCollection::Artwork(),
            default => MediaCollection::Poster(),
        };
    }

    /**
     * Returns a display title for the linked subject.
     *
     * @return string|null
     */
    public function getSubjectTitle(): ?string
    {
        $subject = $this->subject;

        if (!$subject) {
            return null;
        }

        foreach (['title', 'name', 'full_name', 'display_name'] as $attribute) {
            $value = $subject->{$attribute} ?? null;

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    /**
     * Scope for words eligible to be scheduled as a daily puzzle.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeEligibleForSchedule(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function (Builder $query) {
                $query->whereNull('released_at')
                    ->orWhere('released_at', '<=', Carbon::now());
            });
    }

    /**
     * Scope for words whose answer is not held by a current or upcoming daily puzzle.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeSafeToReveal(Builder $query): Builder
    {
        return $query->whereDoesntHave('dailyPuzzle', function (Builder $query) {
            $query->where('puzzle_date', '>=', Carbon::now()->toDateString());
        });
    }
}
