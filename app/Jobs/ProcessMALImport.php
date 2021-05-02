<?php

namespace App\Jobs;

use App\Models\Anime;
use App\Enums\UserLibraryStatus;
use App\Notifications\MALImportFinished;
use App\Models\User;
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
     * @var string
     */
    protected string $behavior;

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
     * @param string $behavior
     */
    public function __construct(User $user, string $xmlContent, string $behavior)
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
        if ($this->behavior === 'overwrite') {
            $this->user->library()->detach();
        }

        // Create XML object
        $xml = simplexml_load_string($this->xmlContent);

        // Read XML object into JSON
        $json = json_encode($xml);
        $json = json_decode($json, true);

        // Loop through the anime in the export file
        foreach($json['anime'] as $anime) {
            $this->handleXMLFileAnime($anime['series_animedb_id'], $anime['my_status']);
        }

        // Notify the user that the MAL import was finished
        $this->user->notify(new MALImportFinished($this->results, $this->behavior));
    }

    /**
     * Handles the importing of a single anime from the XML file.
     *
     * @param int $malID
     * @param string $malStatus
     */
    protected function handleXMLFileAnime(int $malID, string $malStatus)
    {
        // Try to find the Anime in our DB
        $animeMatch = Anime::where('mal_id', $malID)->first();

        // If a match was found
        if ($animeMatch) {
            // Convert the MAL status to one of our own
            $status = $this->convertMALStatus($malStatus);

            // Status not found
            if ($status === null) {
                $this->registerFailure($malID, 'Could not handle status: ' . $malStatus);
                return;
            }

            // Add the anime to their library
            $this->user->library()->attach($animeMatch, ['status' => $status]);

            $this->registerSuccess($animeMatch->id, $malID, $status);
        }
        else $this->registerFailure($malID, 'MAL ID could not be found.');
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
     * Registers a success in the import process.
     *
     * @param int $animeID
     * @param int $malID
     * @param string $status
     */
    protected function registerSuccess(int $animeID, int $malID, string $status)
    {
        $this->results['successful'][] = [
            'anime_id'  => $animeID,
            'mal_id'    => $malID,
            'status'    => $status
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
