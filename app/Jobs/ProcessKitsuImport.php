<?php

namespace App\Jobs;

use App\Enums\ImportBehavior;
use App\Enums\ImportService;
use App\Enums\UserLibraryStatus;
use App\Models\Anime;
use App\Models\MediaRating;
use App\Models\User;
use App\Models\UserLibrary;
use App\Notifications\AnimeImportFinished;
use App\Scopes\TvRatingScope;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class ProcessKitsuImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of tries.
     *
     * @var int
     */
    public int $tries = 1;

    /**
     * The user to whose library the Kitsu data should be imported.
     *
     * @var User
     */
    protected User $user;

    /**
     * The XML content to be imported.
     *
     * @var string
     */
    protected string $xmlContent;

    /**
     * The service of the import action.
     *
     * @var ImportService
     */
    protected ImportService $service;

    /**
     * The behavior of the import action.
     *
     * @var ImportBehavior
     */
    protected ImportBehavior $behavior;

    /**
     * The results of the import action.
     *
     * @var array[]
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
     * @param ImportService $service
     * @param ImportBehavior $behavior
     */
    public function __construct(User $user, string $xmlContent, ImportService $service, ImportBehavior $behavior)
    {
        $this->user = $user;
        $this->xmlContent = $xmlContent;
        $this->service = $service;
        $this->behavior = $behavior;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Wipe current library if behavior is set to overwrite
        if ($this->behavior->value === ImportBehavior::Overwrite) {
            $this->user->library()->detach();
            $this->user->anime_ratings()->delete();
        }

        // Create XML object
        $xml = simplexml_load_string($this->xmlContent);

        // Read XML object into JSON
        $json = json_encode($xml);
        $json = json_decode($json, true);

        // Loop through the anime in the export file
        foreach($json['anime'] as $anime) {
            $animeId = $anime['series_animedb_id'];
            $status = $anime['my_status'];
            $rating = $anime['my_score'] ?? 0;
            $startDate = $anime['my_start_date'];
            $endDate = $anime['my_finish_date'] ?? '0000-00-00';

            // Handle import
            $this->handleXMLFileAnime($animeId, $status, $rating, $startDate, $endDate);
        }

        // Notify the user that the Kitsu import was finished
        $this->user->notify(new AnimeImportFinished($this->results, $this->service, $this->behavior));
    }

    /**
     * Handles the importing of a single anime from the XML file.
     *
     * @param int $malID
     * @param string $malStatus
     * @param int $malRating
     * @param string $malStartDate
     * @param string $malEndDate
     */
    protected function handleXMLFileAnime(int $malID, string $malStatus, int $malRating, string $malStartDate, string $malEndDate)
    {
        // Try to find the Anime in our DB
        $anime = Anime::withoutGlobalScope(new TvRatingScope)->firstWhere('mal_id', $malID);

        // If a match was found
        if (!empty($anime)) {
            // Convert the Kitsu data to our own
            $status = $this->convertKitsuStatus($malStatus);
            $rating = $this->convertKitsuRating($malRating);
            $startDate = $this->convertKitsuDate($malStartDate);
            $endDate = $this->convertKitsuDate($malEndDate);

            // Status not found
            if ($status === null) {
                $this->registerFailure($malID, 'Could not handle status: ' . $malStatus);
                return;
            }

            // Needs end date
            if ($status === UserLibraryStatus::Completed && $endDate === null) {
                $endDate = now();
            }

            // Add the anime to their library
            UserLibrary::updateOrCreate([
                'user_id'   => $this->user->id,
                'anime_id'  => $anime->id,
            ], [
                'status' => $status,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);

            // Updated their anime score
            MediaRating::updateOrCreate([
                'user_id'       => $this->user->id,
                'model_type'    => Anime::class,
                'model_id'      => $anime->id,
            ], [
                'rating' => $rating,
            ]);

            $this->registerSuccess($anime->id, $malID, $status, $rating);
        } else {
            $this->registerFailure($malID, 'Kitsu ID could not be found.');
        }
    }

    /**
     * Converts a Kitsu status string to our library status'.
     *
     * @param string $malStatus
     * @return ?int
     */
    protected function convertKitsuStatus(string $malStatus): ?int
    {
        return match ($malStatus) {
            'Watching' => UserLibraryStatus::Watching,
            'On-Hold' => UserLibraryStatus::OnHold,
            'Plan to Watch' => UserLibraryStatus::Planning,
            'Dropped' => UserLibraryStatus::Dropped,
            'Completed' => UserLibraryStatus::Completed,
            default => null,
        };
    }

    /**
     * Converts and returns Kurozora specific rating.
     *
     * @param int $malRating
     * @return int
     */
    protected function convertKitsuRating(int $malRating): int
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
    protected function convertKitsuDate(string $malDate): ?Carbon
    {
        if ($malDate === '0000-00-00') {
            return now();
        }

        $dateComponents = explode('-', $malDate);
        return Carbon::createFromDate($dateComponents[0], $dateComponents[1], $dateComponents[2]);
    }

    /**
     * Registers a success in the import process.
     *
     * @param int $animeID
     * @param int $malID
     * @param string $status
     * @param int $rating
     */
    protected function registerSuccess(int $animeID, int $malID, string $status, int $rating)
    {
        $this->results['successful'][] = [
            'anime_id'  => $animeID,
            'mal_id'    => $malID,
            'status'    => $status,
            'rating'    => $rating,
        ];
    }

    /**
     * Registers a failure in the import process.
     *
     * @param int $malID
     * @param string $reason
     */
    protected function registerFailure(int $malID, string $reason)
    {
        $this->results['failure'][] = [
            'mal_id'    => $malID,
            'reason'    => $reason
        ];
    }
}