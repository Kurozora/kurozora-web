<?php

namespace App\Jobs;

use App\Enums\ImportBehavior;
use App\Enums\UserLibraryKind;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\User;
use App\Models\UserLibrary;
use App\Notifications\LocalLibraryImportFinished;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class ProcessLocalLibraryImport implements ShouldQueue
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
     * The JSON string to be imported.
     *
     * @var string $jsonString
     */
    protected string $jsonString;

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
     * @param string $jsonString
     * @param ImportBehavior $behavior
     */
    public function __construct(User $user, string $jsonString, ImportBehavior $behavior)
    {
        $this->user = $user;
        $this->jsonString = $jsonString;
        $this->behavior = $behavior;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Decode the JSON string
        $json = json_decode($this->jsonString, true);

        // Wipe current library if behavior is set to overwrite
        if ($this->behavior->value === ImportBehavior::Overwrite) {
            $this->user->clearLibrary();
            $this->user->clearFavorites();
            $this->user->mediaRatings()->forceDelete();
        }

        // Loop through the anime in the export file
        foreach ($json as $entry) {
            $slug = $entry['slug'];
            $libraryKind = UserLibraryKind::fromKey($entry['libraryKind']);
            $status = $entry['libraryCategory'];
            $startDate = $entry['startDate'] ?? '0000-00-00';
            $endDate = $entry['endDate'] ?? '0000-00-00';
            $creationDate = $entry['creationDate'] ?? '0000-00-00';

            // Handle import
            $this->importModel($slug, $libraryKind, $status, $startDate, $endDate, $creationDate);
        }

        $this->user->notify(new LocalLibraryImportFinished($this->results, $this->behavior));
    }

    /**
     * Handles the importing of a single model.
     *
     * @param string             $slug
     * @param UserLibraryKind $libraryKind
     * @param int             $status
     * @param string          $startDate
     * @param string          $endDate
     * @param string          $creationDate
     */
    protected function importModel(string $slug, UserLibraryKind $libraryKind, int $status, string $startDate, string $endDate, string $creationDate): void
    {
        // Try to find the model
        $model = match ($libraryKind->value) {
            UserLibraryKind::Anime => Anime::withoutGlobalScopes()
                ->firstWhere('slug', '=', $slug),
            UserLibraryKind::Manga => Manga::withoutGlobalScopes()
                ->firstWhere('slug', '=', $slug),
            UserLibraryKind::Game => Game::withoutGlobalScopes()
                ->firstWhere('slug', '=', $slug),
        };

        // If a match was not found
        if (empty($model)) {
            logger($libraryKind->description . ' slug: ' . $slug . ' does not exist');
            $this->registerFailure($libraryKind, $slug, 'Slug could not be found.');
            return;
        }

        $startedAt = $this->convertDate($startDate);
        $endedAt = $this->convertDate($endDate);
        $createdAt = $this->convertDate($creationDate);

        // Add entry to their library
        UserLibrary::updateOrCreate([
            'user_id' => $this->user->id,
            'trackable_type' => $model->getMorphClass(),
            'trackable_id' => $model->id,
        ], [
            'status' => $status,
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'created_at' => $createdAt
        ]);

        $this->registerSuccess($libraryKind, $model->id, $slug, $status);
    }

    /**
     * Converts and returns Carbon dates from given string.
     *
     * @param string $date
     * @return Carbon|null
     */
    protected function convertDate(string $date): ?Carbon
    {
        if ($date === '0000-00-00') {
            return now();
        }

        return Carbon::createFromTimestamp($date);
    }

    /**
     * Registers a success in the import process.
     *
     * @param UserLibraryKind $libraryKind
     * @param mixed           $modelID
     * @param string          $slug
     * @param mixed           $status
     */
    protected function registerSuccess(UserLibraryKind $libraryKind, mixed $modelID, string $slug, int $status): void
    {
        $this->results['successful'][] = [
            'library'   => $libraryKind->description,
            'model_id'  => $modelID,
            'slug'      => $slug,
            'status'    => $status
        ];
    }

    /**
     * Registers a failure in the import process.
     *
     * @param UserLibraryKind $libraryKind
     * @param string          $slug
     * @param string          $reason
     */
    protected function registerFailure(UserLibraryKind $libraryKind, string $slug, string $reason): void
    {
        $this->results['failure'][] = [
            'library'   => $libraryKind->description,
            'slug'      => $slug,
            'reason'    => $reason
        ];
    }
}
