<?php

namespace App\Jobs;

use App\Enums\ImportBehavior;
use App\Enums\ImportService;
use App\Enums\UserLibraryKind;
use App\Enums\UserLibraryStatus;
use App\Models\Anime;
use App\Models\Manga;
use App\Models\MediaRating;
use App\Models\User;
use App\Models\UserLibrary;
use App\Notifications\LibraryImportFinished;
use App\Notifications\LibraryImportUnsupported;
use Artisan;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class ProcessMALImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
     * The user to whose library data should be imported.
     *
     * @var User $user
     */
    protected User $user;

    /**
     * The XML content to be imported.
     *
     * @var string $xmlContent
     */
    protected string $xmlContent;

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
     * @param string $xmlContent
     * @param UserLibraryKind $libraryKind
     * @param ImportService $service
     * @param ImportBehavior $behavior
     */
    public function __construct(User $user, string $xmlContent, UserLibraryKind $libraryKind, ImportService $service, ImportBehavior $behavior)
    {
        $this->user = $user;
        $this->xmlContent = $xmlContent;
        $this->libraryKind = $libraryKind;
        $this->service = $service;
        $this->behavior = $behavior;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $success = false;

        // Create XML object
        $xml = simplexml_load_string($this->xmlContent);

        // Read XML object into JSON
        $json = json_encode($xml);
        $json = json_decode($json, true);

        switch ($this->libraryKind->value) {
            case UserLibraryKind::Anime:
                $success = $this->handleAnime($json);
                break;
            case UserLibraryKind::Manga:
                $success = $this->handleManga($json);
                break;
        }

        if ($success) {
            $this->user->notify(new LibraryImportFinished($this->results, $this->libraryKind, $this->service, $this->behavior));
        } else {
            $this->user->notify(new LibraryImportUnsupported($this->results, $this->libraryKind, $this->service, $this->behavior));
        }
    }

    /**
     * Execute the anime job.
     *
     * @param array $json
     * @return bool
     */
    public function handleAnime(array $json): bool
    {
        if (isset($json['anime'])) { // Genuine MAL export
            // Wipe current anime library if behavior is set to overwrite
            if ($this->behavior->value === ImportBehavior::Overwrite) {
                $this->user->clearLibrary(Anime::class);
                $this->user->clearFavorites(Anime::class);
                $this->user->clearReminders(Anime::class);
                $this->user->clearRatings(Anime::class);
            }

            // Loop through the anime in the export file
            foreach ($json['anime'] as $anime) {
                $animeID = $anime['series_animedb_id'];
                $status = $anime['my_status'];
                $rating = $anime['my_score'] ?? 0;
                $startDate = $anime['my_start_date'] ?? '0000-00-00';
                $endDate = $anime['my_finish_date'] ?? '0000-00-00';

                // Handle import
                $this->importModel($animeID, $status, $rating, $startDate, $endDate);
            }
        } else if (isset($json['folder'])) { // 9anime export
            // Wipe current anime library if behavior is set to overwrite
            if ($this->behavior->value === ImportBehavior::Overwrite) {
                $this->user->clearLibrary(Anime::class);
                $this->user->clearFavorites(Anime::class);
                $this->user->clearReminders(Anime::class);
                $this->user->clearRatings(Anime::class);
            }

            // Loop through the anime in the export file
            foreach ($json['folder'] as $folder) {
                $status = $folder['name'];
                $animes = $folder['data']['item'];

                foreach ($animes as $anime) {
                    $animeID = basename($anime['link']);

                    // Handle import
                    $this->importModel($animeID, $status, 0, '0000-00-00', '0000-00-00');
                }
            }
        } else {
            $this->fail('Unsupported anime import file structure.');
            return false;
        }

        return true;
    }

    /**
     * Execute the manga job.
     *
     * @param array $json
     * @return bool
     */
    public function handleManga(array $json): bool
    {
        if (isset($json['manga'])) {
            // Wipe current manga library if behavior is set to overwrite
            if ($this->behavior->value === ImportBehavior::Overwrite) {
                $this->user->clearLibrary(Manga::class);
                $this->user->clearFavorites(Manga::class);
                $this->user->clearReminders(Manga::class);
                $this->user->clearRatings(Manga::class);
            }

            // Loop through the manga in the export file
            foreach ($json['manga'] as $manga) {
                $mangaId = $manga['manga_mangadb_id'];
                $status = $manga['my_status'];
                $rating = $manga['my_score'] ?? 0;
                $startDate = $manga['my_start_date'] ?? '0000-00-00';
                $endDate = $manga['my_finish_date'] ?? '0000-00-00';

                // Handle import
                $this->importModel($mangaId, $status, $rating, $startDate, $endDate);
            }
        } else {
            $this->fail('Unsupported manga import file structure.');
            return false;
        }

        return true;
    }

    /**
     * Handles the importing of a single model from the XML file.
     *
     * @param int $malID
     * @param string $malStatus
     * @param int $malRating
     * @param string $malStartDate
     * @param string $malEndDate
     */
    protected function importModel(int $malID, string $malStatus, int $malRating, string $malStartDate, string $malEndDate): void
    {
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
        $status = $this->convertMALStatus($malStatus);
        $rating = $this->convertMALRating($malRating);
        $startedAt = null;
        $endedAt = null;

        // Status not found
        // NOTE: - Don't use empty() because 'Watching' status is 0 and that returns true.
        if ($status === null) {
            $this->registerFailure($malID, 'Could not handle status: ' . $malStatus);
            return;
        }

        // Check if the anime needs an end date
        switch ($status) {
            case UserLibraryStatus::OnHold:
            case UserLibraryStatus::InProgress:
                $startedAt = $this->convertMALDate($malStartDate) ?? now();
                break;
            case UserLibraryStatus::Dropped:
            case UserLibraryStatus::Completed:
                $endedAt = $this->convertMaLDate($malEndDate) ?? now();
                $startedAt = $this->convertMALDate($malStartDate) ?? now();
                break;
            case UserLibraryStatus::Planning:
            default:
                break;
        }

        // Add the anime to their library
        UserLibrary::updateOrCreate([
            'user_id' => $this->user->id,
            'trackable_type' => $model->getMorphClass(),
            'trackable_id' => $model->id,
        ], [
            'status' => $status,
            'started_at' => $startedAt,
            'ended_at' => $endedAt
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

        $this->registerSuccess($model->id, $malID, $status, $rating);
    }

    /**
     * Converts a MAL status string to our library status.
     *
     * @param string $malStatus
     * @return ?int
     */
    protected function convertMALStatus(string $malStatus): ?int
    {
        $malStatus = str($malStatus)->lower()
            ->camel()
            ->trim()
            ->value();

        return match ($malStatus) {
            'reading', 'watching' => UserLibraryStatus::InProgress,
            'onHold' => UserLibraryStatus::OnHold,
            'planToWatch', 'planToRead' => UserLibraryStatus::Planning,
            'dropped' => UserLibraryStatus::Dropped,
            'completed' => UserLibraryStatus::Completed,
            default => null,
        };
    }

    /**
     * Converts and returns Kurozora specific rating.
     *
     * @param int $malRating
     * @return int
     */
    protected function convertMALRating(int $malRating): int
    {
        if ($malRating == 0) {
            return $malRating;
        }

        return round($malRating) * 0.5;
    }

    /**
     * Converts and returns Carbon dates from given string.
     *
     * @param string $malDate
     * @return Carbon|null
     */
    protected function convertMALDate(string $malDate): ?Carbon
    {
        if ($malDate === '0000-00-00') {
            return now();
        }

        $dateComponents = explode('-', $malDate);
        $date = Carbon::createFromDate($dateComponents[0], $dateComponents[1], $dateComponents[2]);

        if ($date->year == 0000) {
            $date->setYear(now()->year);
        }

        return $date;
    }

    /**
     * Registers a success in the import process.
     *
     * @param mixed $modelID
     * @param int   $malID
     * @param mixed $status
     * @param int   $rating
     */
    protected function registerSuccess(mixed $modelID, int $malID, mixed $status, int $rating): void
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
     * @param int    $malID
     * @param string $reason
     */
    protected function registerFailure(int $malID, string $reason): void
    {
        $this->results['failure'][] = [
            'library'   => $this->libraryKind->description,
            'mal_id'    => $malID,
            'reason'    => $reason
        ];
    }
}
