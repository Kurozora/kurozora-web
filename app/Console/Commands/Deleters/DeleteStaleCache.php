<?php

namespace App\Console\Commands\Deleters;

use App\Models\Cache;
use Illuminate\Console\Command;

class DeleteStaleCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:stale_cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes cache in database that are expired.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Cache::where('expiration', '<', now()->subHours(2)->timestamp)
            ->delete();

        return Command::SUCCESS;
    }
}
