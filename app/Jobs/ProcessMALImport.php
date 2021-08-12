<?php

namespace App\Jobs;

use App\Enums\MALImportBehavior;
use App\Enums\UserLibraryStatus;
use App\Models\Anime;
use App\Models\AnimeRating;
use App\Models\User;
use App\Models\UserLibrary;
use App\Notifications\MALImportFinished;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessMALImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of tries.
     *
     * @var int
     */
    public int $tries = 1;

    /**
     * The user to whose library the MAL data should be imported.
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
     * The behavior of the import action.
     *
     * @var MALImportBehavior
     */
    protected MALImportBehavior $behavior;

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
     * @param MALImportBehavior $behavior
     */
    public function __construct(User $user, string $xmlContent, MALImportBehavior $behavior)
    {
        $this->user = $user;
        $this->xmlContent = $xmlContent;
        $this->behavior = $behavior;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Wipe current library if behavior is set to overwrite
        if ($this->behavior->value === MALImportBehavior::Overwrite) {
            $this->user->library()->detach();
            $this->user->animeRating()->delete();
        }

        // Create XML object
        $xml = simplexml_load_string($this->xmlContent);

        // Read XML object into JSON
        $json = json_encode($xml);
        $json = json_decode($json, true);

        // Loop through the anime in the export file
        foreach($json['anime'] as $anime) {
            $this->handleXMLFileAnime($anime['series_animedb_id'], $anime['my_status'], $anime['my_score']);
        }

        // Notify the user that the MAL import was finished
        $this->user->notify(new MALImportFinished($this->results, $this->behavior));
    }

    /**
     * Handles the importing of a single anime from the XML file.
     *
     * @param int $malID
     * @param string $malStatus
     * @param int $malRating
     */
    protected function handleXMLFileAnime(int $malID, string $malStatus, int $malRating)
    {
        // Try to find the Anime in our DB
        $anime = Anime::withoutGlobalScope('tv_rating')->firstWhere('mal_id', $malID);

        // If a match was found
        if (!empty($anime)) {
            // Convert the MAL data to our own
            $status = $this->convertMALStatus($malStatus);
            $rating = $this->convertMALRating($malRating);

            // Status not found
            if ($status === null) {
                $this->registerFailure($malID, 'Could not handle status: ' . $malStatus);
                return;
            }

            // Add the anime to their library
            UserLibrary::updateOrCreate([
                'user_id' => $this->user->id,
                'anime_id' => $anime->id,
            ], [
                'status' => $status
            ]);

            // Updated their anime score
            AnimeRating::updateOrCreate([
                'user_id' => $this->user->id,
                'anime_id' => $anime->id,
            ], [
                'rating' => $rating,
            ]);

            $this->registerSuccess($anime->id, $malID, $status, $rating);
        } else {
            $this->registerFailure($malID, 'MAL ID could not be found.');
        }
    }

    /**
     * Converts a MAL status string to our library status'.
     *
     * @param string $malStatus
     * @return ?int
     */
    protected function convertMALStatus(string $malStatus): ?int
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
    protected function convertMALRating(int $malRating): int
    {
        if ($malRating == 0) {
            return $malRating;
        }

        return round($malRating) * 0.5;
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
