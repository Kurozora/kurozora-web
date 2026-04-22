<?php

namespace App\Services;

use App\Models\User;

class ReputationService
{
    /**
     * Calculate a user's reputation score.
     *
     * The score combines volume (log-compressed, measures dedication) with
     * a quality multiplier (ratio-based, measures engagement quality).
     * Quality ratios amplify or dampen the volume base, so:
     *
     * - Great ratios + big library = high score
     * - Great ratios + small library = moderate score
     * - Bad ratios + big library = moderate score
     * - Bad ratios + small library = low score
     *
     * This keeps the gap between new and veteran users reasonable.
     */
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

        $completedCount = $user->library_completed_count;
        $inProgressCount = $user->library_in_progress_count ?? 0;
        $planningCount = $user->library_planning_count ?? 0;
        $droppedCount = $user->library_dropped_count ?? 0;
        $libraryCount = max($user->library_count, 1);

        // =====================================================================
        // Volume base (log-compressed)
        // =====================================================================
        $volumeBase =
            10 * log10($completedCount + 1) +                                   // 10→10, 362→25.6, 2289→33.6, 4429→36.5
            8 * log10($user->media_ratings_with_description_count + 1) +        // 8→7.2, 354→20.4, 3000→27.8, 5000→29.6
            0.5 * log10($user->user_watched_episodes_count + 1) +
            0.25 * log10($user->user_rewatched_episodes_count + 1) +
            0.25 * log10($user->view_count + 1);

        // =====================================================================
        // Quality multiplier (ratio-based)
        // =====================================================================

        // Completion rate: what fraction of the library is completed?
        $completionRate = $completedCount / $libraryCount;

        // Review engagement rate: is a review written for what’s completed?
        $reviewRate = min($user->media_ratings_with_description_count / max($completedCount, 1), 1.0);

        // Community participation rate: is the user active in the feed?
        $feedRate = min($user->feed_messages_count, 100) / 100;

        // Rating engagement: are consumed titles at least rated?
        $ratingRate = min($user->media_ratings_without_description_count, 20) / 20;

        // Rewatch dedication: does the user rewatch episodes?
        $rewatchRate = min($user->user_rewatched_episodes_count / max($user->user_watched_episodes_count, 1), 1.0);

        $qualityMultiplier = 0.25
            + ($completionRate * 0.8)
            + ($reviewRate * 0.6)
            + ($feedRate * 0.2)
            + ($ratingRate * 0.1)
            + ($rewatchRate * 0.05);

        // =====================================================================
        // Social tier (sqrt-compressed)
        // =====================================================================
        $socialScore =
            2 * sqrt(min($user->reshares_received_count, 500)) +
            1.5 * sqrt(min($user->replies_received_count, 500)) +
            1.5 * sqrt(min($user->hearts_received_count, 1000)) +
            1.5 * sqrt(min($user->followers_count, 1000));

        // =====================================================================
        // Library health penalties
        // =====================================================================

        // Watching-to-completed ratio: if watching more than 3× completed,
        // dampen the score. User is hoarding, not engaging.
        $inProgressRatio = $inProgressCount / max($completedCount, 1);
        $healthMultiplier = $inProgressRatio > 3
            ? max(0.25, 1 - (($inProgressRatio - 3) * 0.1))
            : 1.0;

        // Dropped ratio: if more than half the library is dropped, dampen the score.
        $droppedRatio = $droppedCount / $libraryCount;
        $healthMultiplier *= $droppedRatio > 0.5
            ? max(0.5, 1 - (($droppedRatio - 0.5) * 0.5))
            : 1.0;

        // Planning graveyard: excessive planning relative to completed.
        $planningPenalty = 0;

        if ($planningCount > 50 && $planningCount > max($completedCount, 1) * 5) {
            $planningPenalty = 0.5 * ($planningCount - max($completedCount, 1) * 5);
        }

        // Block penalty: direct subtraction per block received.
        $blockPenalty = $user->blocked_by_count * 3;

        // =====================================================================
        // Final score
        // =====================================================================
        $score = ($volumeBase * $qualityMultiplier * $healthMultiplier)
            + $socialScore
            - $planningPenalty
            - $blockPenalty;

        // Floor at 0
        return max(0, (int) round($score));
    }
}
