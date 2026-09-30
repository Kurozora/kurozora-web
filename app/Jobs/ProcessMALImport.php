<?php

namespace App\Jobs;

use App\Enums\ImportBehavior;
use App\Enums\ImportService;
use App\Enums\UserLibraryKind;
use App\Enums\UserLibraryStatus;
use App\Jobs\Concerns\ReportsLibraryImportProgress;
use App\Models\Anime;
use App\Models\Episode;
use App\Models\Manga;
use App\Models\MediaRating;
use App\Models\Season;
use App\Models\User;
use App\Models\UserLibrary;
use App\Models\UserWatchedEpisode;
use App\Notifications\LibraryImportFinished;
use Artisan;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class ProcessMALImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, ReportsLibraryImportProgress, SerializesModels;

    /**
     * The number of tries.
     *
     * @var int $tries
     */
    public int $tries = 1;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int $timeout
     */
    public int $timeout = 0;

    /**
     * The largest rewatch count a user can set.
     *
     * @var int
     */
    protected const int MAX_REWATCH_COUNT = 100;

    /**
     * The user to whose library data should be imported.
     *
     * @var User $user
     */
    protected User $user;

    /**
     * The entries to be imported.
     *
     * @var array $entries
     */
    protected array $entries;

    /**
     * The library of the import action.
     *
     * @var UserLibraryKind $libraryKind
     */
    protected UserLibraryKind $libraryKind;

    /**
     * The service of the import action.
     *
     * @var ImportService $service
     */
    protected ImportService $service;

    /**
     * The behavior of the import action.
     *
     * @var ImportBehavior $behavior
     */
    protected ImportBehavior $behavior;

    /**
     * The results of the import action.
     *
     * @var array[] $results
     */
    protected array $results = [
        'successful'    => [],
        'failure'       => []
    ];

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param array $entries
     * @param UserLibraryKind $libraryKind
     * @param ImportService $service
     * @param ImportBehavior $behavior
     */
    public function __construct(User $user, array $entries, UserLibraryKind $libraryKind, ImportService $service, ImportBehavior $behavior)
    {
        $this->user = $user;
        $this->entries = $entries;
        $this->libraryKind = $libraryKind;
        $this->service = $service;
        $this->behavior = $behavior;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->user->withSingleStateBump(function (): void {
            $modelClass = match ($this->libraryKind->value) {
                UserLibraryKind::Manga => Manga::class,
                default => Anime::class,
            };

            // Wipe current library if behavior is set to overwrite
            if ($this->behavior->value === ImportBehavior::Overwrite) {
                $this->user->clearLibrary($modelClass);
                $this->user->clearFavorites($modelClass);
                $this->user->clearReminders($modelClass);
                $this->user->clearRatings($modelClass);
                $this->user->clearNotes($modelClass);
            }

            $existingStartDates = UserLibrary::where('user_id', '=', $this->user->id)
                ->where('trackable_type', '=', $modelClass)
                ->whereNotNull('started_at')
                ->pluck('started_at', 'trackable_id')
                ->all();

            $this->startImportProgress(count($this->entries));

            foreach ($this->entries as $entry) {
                $this->importEntry($entry, $existingStartDates);
                $this->advanceImportProgress();
            }
        });

        $this->user->notify(new LibraryImportFinished($this->results, $this->libraryKind, $this->service, $this->behavior));
    }

    /**
     * Handles the importing of a single entry.
     *
     * @param array $entry
     * @param array $existingStartDates
     */
    protected function importEntry(array $entry, array $existingStartDates): void
    {
        $malID = $entry['mal_id'];

        // Skip records where id is not numeric
        if ($malID === null) {
            $this->registerFailure(null, 'MAL ID is not a valid number.');
            return;
        }

        // Try to find the model in our DB
        $model = match ($this->libraryKind->value) {
            UserLibraryKind::Manga => Manga::withoutGlobalScopes()
                ->firstWhere('mal_id', '=', $malID),
            default => Anime::withoutGlobalScopes()
                ->firstWhere('mal_id', '=', $malID)
        };

        // If a match was not found
        if (empty($model)) {
            switch ($this->libraryKind->value) {
                case UserLibraryKind::Anime:
                    Artisan::call('scrape:mal_anime', ['malID' => $malID]);
                    break;
                case UserLibraryKind::Manga:
                    Artisan::call('scrape:mal_manga', ['malID' => $malID]);
                    break;
                default: break;
            }

            // Retry to find the model in our DB
            $model = match ($this->libraryKind->value) {
                UserLibraryKind::Manga => Manga::withoutGlobalScopes()
                    ->firstWhere('mal_id', $malID),
                default => Anime::withoutGlobalScopes()
                    ->firstWhere('mal_id', $malID)
            };

            if (empty($model)) {
                logger($this->libraryKind->description . ' mal_id: ' . $malID . ' does not exist');
                $this->registerFailure($malID, 'MAL ID could not be found.');
                return;
            }
        }

        // Convert the MAL data to our own
        $status = $entry['status'] ?? $this->inferStatus($entry);
        $rating = $this->convertMALRating($entry['score']);
        $startedAt = null;
        $endedAt = null;

        // Check if the anime needs an end date
        switch ($status) {
            case UserLibraryStatus::OnHold:
            case UserLibraryStatus::InProgress:
                $startedAt = $this->convertMALDate($entry['started_at']);
                break;
            case UserLibraryStatus::Dropped:
            case UserLibraryStatus::Completed:
                $endedAt = $this->convertMALDate($entry['ended_at']);
                $startedAt = $this->convertMALDate($entry['started_at']);
                break;
            case UserLibraryStatus::Planning:
            default:
                break;
        }

        if ($startedAt !== null && isset($existingStartDates[$model->id])) {
            $startedAt = $startedAt->min($existingStartDates[$model->id]);
        }

        // Add the anime to their library
        UserLibrary::withTrashed()->updateOrCreate([
            'user_id' => $this->user->id,
            'trackable_type' => $model->getMorphClass(),
            'trackable_id' => $model->id,
        ], [
            'status' => $status,
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'deleted_at' => null,
            ...$this->trackingAttributes($entry),
        ]);

        // Updated their anime score
        if (!empty($rating)) {
            MediaRating::updateOrCreate([
                'user_id' => $this->user->id,
                'model_type' => $model->getMorphClass(),
                'model_id' => $model->id,
            ], [
                'rating' => $rating,
            ]);
        }

        if ($entry['note'] !== null) {
            $this->importNote($model, $entry['note']);
        }

        if ($model instanceof Anime && $entry['progress'] > 0) {
            $this->markEpisodesWatched($model, $entry['progress'], $endedAt ?? $startedAt ?? now());
        }

        $this->registerSuccess($model->id, $malID, $status, $rating);
    }

    /**
     * Returns the library attributes the given entry provides.
     *
     * @param array $entry
     * @return array
     */
    protected function trackingAttributes(array $entry): array
    {
        $isOverwrite = $this->behavior->value === ImportBehavior::Overwrite;
        $trackingAttributes = [
            'rewatch_count' => $entry['rewatch_count'] === null
                ? ($isOverwrite ? 0 : null)
                : min($entry['rewatch_count'], self::MAX_REWATCH_COUNT),
            'is_rewatching' => $entry['is_rewatching'] ?? ($isOverwrite ? false : null),
            'rewatch_value' => $entry['rewatch_value'],
            'priority' => $entry['priority'],
            'storage' => $entry['storage'],
            'storage_amount' => $entry['storage_amount'],
            'tags' => $entry['tags'],
        ];

        if ($isOverwrite) {
            return $trackingAttributes;
        }

        return array_filter($trackingAttributes, fn (mixed $value): bool => $value !== null);
    }

    /**
     * Adds the given imported note to the user's note on the given model.
     *
     * @param Model  $model
     * @param string $note
     */
    protected function importNote(Model $model, string $note): void
    {
        $note = trim(strip_tags($note));
        $existingNote = (string) $this->user->noteFor($model)?->body;

        if ($note === '' || str_contains($existingNote, $note)) {
            return;
        }

        if ($existingNote === '') {
            $this->user->setNote($model, $note);
            return;
        }

        $importHeader = __('Imported from :service on :date:', [
            'service' => $this->service->description,
            'date' => now()->toDateString(),
        ]);

        $this->user->setNote($model, $existingNote . "\n\n" . $importHeader . "\n" . $note);
    }

    /**
     * Returns the library status inferred from the given entry.
     *
     * @param array $entry
     * @return int
     */
    protected function inferStatus(array $entry): int
    {
        if (!empty($entry['ended_at'])) {
            return UserLibraryStatus::Completed;
        }

        if (!empty($entry['started_at']) || $entry['progress'] > 0) {
            return UserLibraryStatus::InProgress;
        }

        return UserLibraryStatus::Planning;
    }

    /**
     * Marks the given number of the anime's first episodes as watched.
     *
     * @param Anime  $anime
     * @param int    $episodeCount
     * @param Carbon $completedAt
     */
    protected function markEpisodesWatched(Anime $anime, int $episodeCount, Carbon $completedAt): void
    {
        $episodeIDs = $anime->episodes()
            ->whereNull(Episode::TABLE_NAME . '.deleted_at')
            ->whereNull(Season::TABLE_NAME . '.deleted_at')
            ->where(Season::TABLE_NAME . '.number', '>', 0)
            ->where(Episode::TABLE_NAME . '.is_special', '=', false)
            ->orderBy(Season::TABLE_NAME . '.number')
            ->orderBy(Episode::TABLE_NAME . '.number')
            ->limit($episodeCount)
            ->pluck(Episode::TABLE_NAME . '.id');

        if ($episodeIDs->isEmpty()) {
            return;
        }

        $completedAttributes = array_merge(UserWatchedEpisode::completedAttributes(), [
            'completed_at' => $completedAt,
        ]);
        $existingIDs = $this->user->userWatchedEpisodes()
            ->whereIn('episode_id', $episodeIDs)
            ->pluck('episode_id');

        $this->user->episodes()->attach($episodeIDs->diff($existingIDs), $completedAttributes);

        $this->user->userWatchedEpisodes()
            ->whereIn('episode_id', $episodeIDs)
            ->whereNull('completed_at')
            ->update($completedAttributes);
    }

    /**
     * Converts and returns Kurozora specific rating.
     *
     * @param int $malRating
     * @return float
     */
    protected function convertMALRating(int $malRating): float
    {
        return $malRating * 0.5;
    }

    /**
     * Converts and returns Carbon dates from given string.
     *
     * @param null|string $malDate
     * @return Carbon
     */
    protected function convertMALDate(?string $malDate): Carbon
    {
        if (empty($malDate)) {
            return now();
        }

        [$year, $month, $day] = array_map('intval', explode('-', $malDate));

        return Carbon::createFromDate($year ?: now()->year, max($month, 1), max($day, 1));
    }

    /**
     * Registers a success in the import process.
     *
     * @param mixed $modelID
     * @param int   $malID
     * @param mixed $status
     * @param float $rating
     */
    protected function registerSuccess(mixed $modelID, int $malID, mixed $status, float $rating): void
    {
        $this->results['successful'][] = [
            'library'   => $this->libraryKind->description,
            'model_id'  => $modelID,
            'mal_id'    => $malID,
            'status'    => $status,
            'rating'    => $rating,
        ];
    }

    /**
     * Registers a failure in the import process.
     *
     * @param ?int   $malID
     * @param string $reason
     */
    protected function registerFailure(?int $malID, string $reason): void
    {
        $this->results['failure'][] = [
            'library'   => $this->libraryKind->description,
            'mal_id'    => $malID,
            'reason'    => $reason
        ];
    }
}
