<?php

namespace App\Console\Commands\Calculators;

use App\Models\User;
use App\Services\ReputationService;
use Closure;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Helper\ProgressBar;

class CalculateUserReputation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:user_reputation
                            {id? : the id(s) of the user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates the given user’s reputation.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $userIDs = $this->argument('id');

        if (empty($userIDs)) {
            // Get users that had a session or token activity within the last 7 days
            $createdAt = now()->startOfDay()->subDays(7);
            $userQuery = User::select([User::TABLE_NAME . '.id', User::TABLE_NAME . '.view_count'])
                ->where(function ($query) use ($createdAt) {
                    $query->where('created_at', '>=', $createdAt)
                        ->orWhereRelation('sessions', 'last_activity', '>=', $createdAt->timestamp)
                        ->orWhereRelation('tokens', 'last_used_at', '>=', $createdAt);
                });
            $count = $userQuery->count();

            // Track progress
            $bar = $this->output->createProgressBar($count);

            User::withoutSyncingToSearch(function () use ($bar, $userQuery) {
                // Calculate reputation
                $userQuery->groupBy([User::TABLE_NAME . '.id', User::TABLE_NAME . '.view_count'])
                    ->withCount([
                        'library_completed as library_completed_count',
                        'user_watched_episodes',
                        'user_rewatched_episodes',
                        'library',
                        'feed_messages',
                        'reshares_received',
                        'replies_received',
                        'hearts_received',
                        'media_ratings_without_description',
                        'media_ratings_with_description',
                        'followers',
                        'blocked',
                    ])
                    ->chunkById(500, $this->chunkById($bar), 'id');
            });
        } else {
            $userIDs = is_array($userIDs) ? $userIDs : explode(',', $userIDs);

            // Track progress
            $bar = $this->output->createProgressBar(count($userIDs));

            // Calculate reputation
            User::withoutSyncingToSearch(function () use ($bar, $userIDs) {
                User::select([User::TABLE_NAME . '.id', User::TABLE_NAME . '.view_count'])
                    ->whereIn('id', $userIDs)
                    ->groupBy([User::TABLE_NAME . '.id', User::TABLE_NAME . '.view_count'])
                    ->withCount([
                        'library_completed',
                        'user_watched_episodes',
                        'user_rewatched_episodes',
                        'library',
                        'feed_messages',
                        'reshares_received',
                        'replies_received',
                        'hearts_received',
                        'media_ratings_without_description',
                        'media_ratings_with_description',
                        'followers',
                        'blocked_by',
                    ])
                    ->chunkById(500, $this->chunkById($bar), 'id');
            });
        }

        return Command::SUCCESS;
    }

    /**
     * Chunk by ID and calculate reputation for each user.
     *
     * @param ProgressBar $bar
     *
     * @return Closure
     */
    private function chunkById(ProgressBar $bar): Closure
    {
        return function (Collection $users) use ($bar) {
            DB::transaction(function () use ($bar, $users) {
                foreach ($users as $user) {
                    $score = app(ReputationService::class)
                        ->calculate($user);

                    $user->updateQuietly([
                        'reputation_count' => (int) round($score),
                    ]);

                    $bar->advance();
                }
            });
        };
    }
}
