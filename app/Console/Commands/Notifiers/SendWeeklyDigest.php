<?php

namespace App\Console\Commands\Notifiers;

use App\Models\User;
use App\Models\UserLibrary;
use App\Notifications\NewDigest;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Notification;
use Laravel\Telescope\Telescope;
use Pulse;

class SendWeeklyDigest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-weekly-digest {--user= : Only notify this user id (for testing)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify users who track something that their weekly digest is ready.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Pulse::stopRecording();
        Telescope::stopRecording();

        $notifiedCount = 0;

        User::whereIn('id', UserLibrary::where('is_hidden', '=', false)->select('user_id')->distinct())
            ->when($this->option('user'), fn ($query, $userID) => $query->whereKey($userID))
            ->chunkById(500, function (Collection $users) use (&$notifiedCount) {
                Notification::send($users, new NewDigest);
                $notifiedCount += $users->count();
            });

        $this->info('Sent the weekly digest to ' . $notifiedCount . ' user(s).');

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }
}
