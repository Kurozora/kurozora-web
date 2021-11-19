<?php

namespace App\Jobs;

use App\Enums\UserLibraryStatus;
use App\Models\Anime;
use App\Models\MediaStat;
use App\Models\UserLibrary;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateMediaStatsJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The object containing the media data.
     *
     * @var UserLibrary
     */
    protected UserLibrary $userLibrary;

    /**
     * Create a new job instance.
     *
     * @var UserLibrary $userLibrary
     * @return void
     */
    public function __construct(UserLibrary $userLibrary)
    {
        $this->userLibrary = $userLibrary;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // If nothing has changed then stop the operation.
        if (!$this->userLibrary->isDirty('status')) {
            return;
        }

        // Find or create media stat for the anime.
        $mediaStat = MediaStat::firstOrCreate([
            'model_type'    => Anime::class,
            'model_id'      => $this->userLibrary->anime_id,
        ]);

        // Decide the old column status.
        $oldColumn = match ($this->userLibrary->getOriginal('status')) {
            UserLibraryStatus::Watching => 'watching_count',
            UserLibraryStatus::Dropped => 'dropped_count',
            UserLibraryStatus::Planning => 'planning_count',
            UserLibraryStatus::Completed => 'completed_count',
            UserLibraryStatus::OnHold => 'on_hold_count',
            default => ''
        };

        // Decide the new column status.
        $newColumn = match ($this->userLibrary->status) {
            UserLibraryStatus::Watching => 'watching_count',
            UserLibraryStatus::Dropped => 'dropped_count',
            UserLibraryStatus::Planning => 'planning_count',
            UserLibraryStatus::Completed => 'completed_count',
            UserLibraryStatus::OnHold => 'on_hold_count',
            default => ''
        };

        // If old column is not present, then a new instance was created. Just increment the new column count.
        // if somehow the anime doesn't have an accompanying stat model, then create one.
        if (empty($oldColumn) || empty($mediaStat->{$oldColumn})) {
            $mediaStat->update([
                $newColumn => $mediaStat->{$newColumn} + 1,
            ]);
        } else {
            // If old column is present, then update both old and new column counts.
            $mediaStat->update([
                $oldColumn => $mediaStat->{$oldColumn} - 1,
                $newColumn => $mediaStat->{$newColumn} + 1,
            ]);
        }
    }
}
