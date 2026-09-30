<?php

namespace App\Console\Commands\Fixers;

use DB;
use Illuminate\Console\Command;
use Laravel\Telescope\Telescope;
use Pulse;

class FeedMessageCounters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:feed_message_counters';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfills feed_messages.replies_count and re_shares_count from source tables';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Pulse::stopRecording();
        Telescope::stopRecording();

        $this->info('Backfilling replies_count…');
        DB::statement('
            UPDATE feed_messages fm
            LEFT JOIN (
                SELECT parent_feed_message_id, COUNT(*) AS c
                FROM feed_messages
                WHERE is_reply = 1 AND parent_feed_message_id IS NOT NULL
                GROUP BY parent_feed_message_id
            ) r ON r.parent_feed_message_id = fm.id
            SET fm.replies_count = COALESCE(r.c, 0)
        ');

        $this->info('Backfilling re_shares_count…');
        DB::statement('
            UPDATE feed_messages fm
            LEFT JOIN (
                SELECT parent_feed_message_id, COUNT(*) AS c
                FROM feed_messages
                WHERE is_reshare = 1 AND parent_feed_message_id IS NOT NULL
                GROUP BY parent_feed_message_id
            ) s ON s.parent_feed_message_id = fm.id
            SET fm.re_shares_count = COALESCE(s.c, 0)
        ');

        Pulse::startRecording();
        Telescope::startRecording();

        $this->info('Done.');

        return Command::SUCCESS;
    }
}
