<?php

namespace App\Console\Commands\Notifiers;

use App\Enums\MediaCollection;
use App\Enums\ScheduledNotificationStatus;
use App\Enums\ScheduledNotificationType;
use App\Enums\UserLibraryStatus;
use App\Models\Anime;
use App\Models\Episode;
use App\Models\ScheduledNotification;
use App\Models\User;
use App\Models\UserLibrary;
use App\Notifications\NewEpisode;
use App\Notifications\NewEpisodes;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Notification;
use Laravel\Telescope\Telescope;
use Pulse;

class DispatchScheduledNotifications extends Command
{
    /**
     * The most plans a single run will process before yielding to the next run.
     *
     * @var int
     */
    private const int BATCH_LIMIT = 500;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:dispatch-scheduled {--user= : Only notify this user id (for testing)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch scheduled notifications whose time has come.';

    /**
     * The library statuses whose owners receive new episode notifications.
     *
     * @var array
     */
    private array $notifiableStatuses = [
        UserLibraryStatus::InProgress,
        UserLibraryStatus::Planning,
        UserLibraryStatus::Interested,
    ];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Pulse::stopRecording();
        Telescope::stopRecording();

        $now = Carbon::now();

        $duePlans = ScheduledNotification::where('type', '=', ScheduledNotificationType::NewEpisode)
            ->where('status', '=', ScheduledNotificationStatus::Pending)
            ->where('scheduled_at', '<=', $now)
            ->with(['subject' => function ($morphTo) {
                $morphTo->constrain([
                    Episode::class => fn ($query) => $query->with(['anime' => fn ($animeQuery) => $animeQuery->with('media')]),
                ]);
            }])
            ->orderBy('scheduled_at')
            ->limit(self::BATCH_LIMIT)
            ->get();

        if ($duePlans->count() === self::BATCH_LIMIT) {
            $this->warn('Dispatch hit the ' . self::BATCH_LIMIT . '-plan cap; the remainder runs next tick.');
        }

        // Group firing plans by their anime so a simulcast dump becomes one push.
        $groups = $this->groupPlansByAnime($duePlans, $now);
        $dispatchedCount = 0;

        foreach ($groups as $group) {
            $this->notifyTrackers($group['anime'], $group['episodes']);

            foreach ($group['plans'] as $plan) {
                $plan->status = ScheduledNotificationStatus::Dispatched();
                $plan->dispatched_at = $now;
                $plan->save();
                $dispatchedCount++;
            }
        }

        $this->info('Dispatched ' . $dispatchedCount . ' scheduled notification(s).');

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }

    /**
     * Group the due plans by anime, settling the plans that must not fire in place.
     *
     * @param Collection $duePlans
     * @param Carbon     $now
     *
     * @return array
     */
    private function groupPlansByAnime(Collection $duePlans, Carbon $now): array
    {
        $groups = [];

        foreach ($duePlans as $plan) {
            $episode = $plan->subject;

            if (!$episode instanceof Episode || $episode->anime === null) {
                $plan->status = ScheduledNotificationStatus::Cancelled();
                $plan->save();
                continue;
            }

            $airTime = $episode->started_at;

            // Broadcast delayed or air time cleared since planning — hold the plan,
            // resync its time, and revisit on a later run rather than firing early.
            if ($airTime === null || $airTime->isFuture()) {
                if ($airTime !== null && !$plan->scheduled_at->equalTo($airTime)) {
                    $plan->scheduled_at = $airTime;
                    $plan->save();
                }
                continue;
            }

            // Aired too long ago to be worth a "new episode" push (e.g. after downtime).
            if ($airTime->lt($now->copy()->subDays(3))) {
                $plan->status = ScheduledNotificationStatus::Superseded();
                $plan->save();
                continue;
            }

            $anime = $episode->anime;
            $animeKey = $anime->getKey();

            if (!isset($groups[$animeKey])) {
                $groups[$animeKey] = ['anime' => $anime, 'plans' => [], 'episodes' => []];
            }

            $groups[$animeKey]['plans'][] = $plan;
            $groups[$animeKey]['episodes'][] = $episode;
        }

        return $groups;
    }

    /**
     * Notify the users actively tracking the anime of its aired episode(s).
     *
     * @param Anime $anime
     * @param array $episodes
     *
     * @return void
     */
    private function notifyTrackers(Anime $anime, array $episodes): void
    {
        $notification = $this->buildNotification($anime, $episodes);

        UserLibrary::where('trackable_type', '=', $anime->getMorphClass())
            ->where('trackable_id', '=', $anime->getKey())
            ->where('is_hidden', '=', false)
            ->whereIn('status', $this->notifiableStatuses)
            ->when($this->option('user'), fn ($query, $userID) => $query->where('user_id', '=', $userID))
            ->chunkById(500, function (Collection $libraryEntries) use ($notification) {
                $users = User::whereIn('id', $libraryEntries->pluck('user_id'))
                    ->get();

                Notification::send($users, $notification);
            });
    }

    /**
     * Build the single or batched notification for the anime's aired episode(s).
     *
     * @param Anime $anime
     * @param array $episodes
     *
     * @return NewEpisode|NewEpisodes
     */
    private function buildNotification(Anime $anime, array $episodes): NewEpisode|NewEpisodes
    {
        $animeTitles = $anime->translations()
            ->whereNotNull('title')
            ->pluck('title', 'locale')
            ->all();
        $fallbackTitle = $anime->original_title;
        $posterImageURL = $anime->getFirstMediaFullUrl(MediaCollection::Poster());

        if (count($episodes) === 1) {
            $episode = $episodes[0];

            return new NewEpisode($episode->public_id, $episode->number, $anime->slug, $animeTitles, $fallbackTitle, $posterImageURL);
        }

        $episodeSummaries = array_map(static fn (Episode $episode): array => [
            'episodeID' => $episode->public_id,
            'number' => $episode->number,
        ], $episodes);

        return new NewEpisodes($anime->slug, $animeTitles, $fallbackTitle, $posterImageURL, $episodeSummaries);
    }
}
