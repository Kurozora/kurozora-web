<?php

namespace App\Services;

use App\Models\User;

class ReputationService
{
    public function calculate(User $user): int
    {
        if (count(array_filter([
            $user->library_completed_count,
            $user->user_watched_episodes_count,
            $user->user_rewatched_episodes_count,
            $user->library_count,
            $user->view_count,
            $user->feed_messages_count,
            $user->reshares_received_count,
            $user->replies_received_count,
            $user->hearts_received_count,
            $user->media_ratings_without_description_count,
            $user->media_ratings_with_description_count,
            $user->followers_count,
            $user->blocked_by_count,
        ], fn($count) => $count > 0)) === 0) {
            return 0;
        }

        return
            $user->library_completed_count +
            0.5 * log10($user->user_watched_episodes_count + 1) +
            0.25 * log10($user->user_rewatched_episodes_count + 1) +
            0.25 * log10($user->library_count + 1) +
            0.25 * log10($user->view_count + 1) +
            min($user->feed_messages_count, 100) +
            ($user->reshares_received_count * 2) +
            ($user->replies_received_count * 1.5) +
            ($user->hearts_received_count * 1.5) +
            min($user->media_ratings_without_description_count, 20) * 0.25 +
            ($user->media_ratings_with_description_count * 2) +
            ($user->followers_count * 1.5) -
            ($user->blocked_by_count * 3);
    }
}
