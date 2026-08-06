<?php

namespace App\Console\Commands\Notifiers;

use App\Enums\ScheduledNotificationStatus;
use App\Enums\ScheduledNotificationType;
use App\Models\Episode;
use App\Models\ScheduledNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Telescope\Telescope;
use Pulse;

class PlanUpcomingEpisodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:plan-upcoming-episodes {--days=14 : How many days ahead to plan} {--since=0 : Also plan episodes that aired within this many days back (for testing)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or resync scheduled new-episode notifications for upcoming episodes.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Pulse::stopRecording();
        Telescope::stopRecording();

        $days = (int) $this->option('days');
        $horizon = Carbon::now()->addDays($days);
        $since = Carbon::now()->subDays((int) $this->option('since'));
        $plannedCount = 0;

        Episode::whereNotNull('started_at')
            ->whereBetween('started_at', [$since, $horizon])
            ->chunkById(1000, function (Collection $episodes) use (&$plannedCount) {
                foreach ($episodes as $episode) {
                    if ($this->planEpisode($episode)) {
                        $plannedCount++;
                    }
                }
            });

        $this->info('Planned ' . $plannedCount . ' upcoming episode notification(s).');

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }

    /**
     * Create the pending plan for the episode, or resync its air time.
     *
     * @param Episode $episode
     *
     * @return bool
     */
    private function planEpisode(Episode $episode): bool
    {
        $plan = ScheduledNotification::where('type', '=', ScheduledNotificationType::NewEpisode)
            ->where('subject_type', '=', $episode->getMorphClass())
            ->where('subject_id', '=', $episode->getKey())
            ->first();

        if ($plan === null) {
            $plan = new ScheduledNotification;
            $plan->type = ScheduledNotificationType::NewEpisode();
            $plan->subject_type = $episode->getMorphClass();
            $plan->subject_id = $episode->getKey();
            $plan->status = ScheduledNotificationStatus::Pending();
        } else if ($plan->status->isNot(ScheduledNotificationStatus::Pending())) {
            return false;
        }

        $plan->scheduled_at = $episode->started_at;

        if (!$plan->isDirty()) {
            return false;
        }

        $plan->save();

        return true;
    }
}
