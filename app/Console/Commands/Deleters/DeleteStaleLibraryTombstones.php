<?php

namespace App\Console\Commands\Deleters;

use App\Models\UserLibrary;
use Illuminate\Console\Command;

class DeleteStaleLibraryTombstones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:stale_library_tombstones';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hard-deletes user library tombstones older than the configured retention horizon.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $expiredBefore = now()->subDays((int) config('library.tombstone_retention_days', 90));

        // Bulk soft-deletes bypass model events, so sweep the search index directly.
        UserLibrary::onlyTrashed()->unsearchable();

        // `onlyTrashed()` lifts the SoftDeletes scope to reach tombstones.
        $count = UserLibrary::onlyTrashed()
            ->where('deleted_at', '<', $expiredBefore)
            ->forceDelete();

        $this->comment($count . ' stale library tombstones deleted');

        return Command::SUCCESS;
    }
}
