<?php

namespace App\Console\Commands\Deleters;

use App\Models\LinkPreview;
use Illuminate\Console\Command;

class DeleteStaleLinkPreviews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:stale_link_previews';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete stale link preview cache older than 30 days';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $deleted = LinkPreview::where('fetched_at', '<', now()->subDays(30))
            ->delete();
        $this->info("Deleted $deleted stale link previews.");

        return Command::SUCCESS;
    }
}
