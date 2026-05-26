<?php

namespace App\Console\Commands\Notifiers;

use App\Models\Timeout;
use App\Notifications\UserTimeoutExpired;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Laravel\Telescope\Telescope;
use Pulse;

class NotifyExpiredTimeouts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timeouts:notify-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify users whose moderation timeout has just expired.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Pulse::stopRecording();
        Telescope::stopRecording();

        $expiredTimeouts = Timeout::query()
            ->whereNull('revoked_at')
            ->whereNull('expiry_notified_at')
            ->where('is_permanent', false)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', Carbon::now())
            ->with('user')
            ->get();

        $notifiedCount = 0;

        foreach ($expiredTimeouts as $timeout) {
            if ($timeout->user === null) {
                continue;
            }

            $timeout->update(['expiry_notified_at' => Carbon::now()]);
            $timeout->user->notify(new UserTimeoutExpired($timeout));
            $notifiedCount++;
        }

        $this->info('Notified ' . $notifiedCount . ' user(s) of expired timeouts.');

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }
}
