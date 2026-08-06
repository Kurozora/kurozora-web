<?php

namespace App\Console\Commands\Deleters;

use Illuminate\Console\Command;
use Lukeraymonddowning\Honey\Models\Spammer;

class DeleteExpiredSpammerBlocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:expired_spammer_blocks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes spammer records whose block or last attempt is older than the configured TTL.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        // Resolve the TTL window for spammer records
        $expiredBefore = now()->subDays((int) config('honey.spammer_blocking.block_ttl_days', 14));

        // Expire blocks past their TTL and discard stale, never-blocked attempts
        $count = Spammer::where('blocked_at', '<', $expiredBefore)
            ->orWhere(function ($query) use ($expiredBefore) {
                $query->whereNull('blocked_at')
                    ->where('updated_at', '<', $expiredBefore);
            })
            ->delete();

        $this->comment($count . ' expired spammer records deleted');

        return Command::SUCCESS;
    }
}
