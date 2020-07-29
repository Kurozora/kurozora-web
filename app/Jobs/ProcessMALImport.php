<?php

namespace App\Jobs;

use App\Anime;
use App\Enums\UserLibraryStatus;
use App\Notifications\MALImportFinished;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessMALImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    protected $user;
    protected $xmlContent;
    protected $behavior;
    protected $results = [
        'successful'    => [],
        'failure'       => []
    ];

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param $xmlContent
     * @param $behavior
     */
    public function __construct(User $user, $xmlContent, $behavior)
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
        if($this->behavior === 'overwrite') {
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
     * @param $malID
     * @param $malStatus
     */
    protected function handleXMLFileAnime($malID, $malStatus) {
        // Try to find the Anime in our DB
        $animeMatch = Anime::where('mal_id', $malID)->first();

        // If a match was found
        if($animeMatch) {
            // Convert the MAL status to one of our own
            $status = $this->convertMALStatus($malStatus);

            // Status not found
            if($status === null) {
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
     * @param $statusString
     * @return int|null
     */
    protected function convertMALStatus($statusString) {
        switch($statusString) {
            case "Watching":        return UserLibraryStatus::Watching;
            case "On-Hold":         return UserLibraryStatus::OnHold;
            case "Plan to Watch":   return UserLibraryStatus::Planning;
            case "Dropped":         return UserLibraryStatus::Dropped;
            case "Completed":       return UserLibraryStatus::Completed;
            default:                return null;
        }
    }

    /**
     * Registers a success in the import process.
     *
     * @param $animeID
     * @param $malID
     * @param $status
     */
    protected function registerSuccess($animeID, $malID, $status) {
        $this->results['successful'][] = [
            'anime_id'  => $animeID,
            'mal_id'    => $malID,
            'status'    => $status
        ];
    }

    /**
     * Registers a failure in the import process.
     *
     * @param $malID
     * @param $reason
     */
    protected function registerFailure($malID, $reason) {
        $this->results['failure'][] = [
            'mal_id'    => $malID,
            'reason'    => $reason
        ];
    }
}
