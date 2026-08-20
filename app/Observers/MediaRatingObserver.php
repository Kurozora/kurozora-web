<?php

namespace App\Observers;

use App\Models\MediaRating;
use App\Models\MediaRatingRevision;

class MediaRatingObserver
{
    /**
     * Archives the superseded version of a rewritten review.
     *
     * @param MediaRating $mediaRating
     *
     * @return void
     */
    public function updating(MediaRating $mediaRating): void
    {
        if (!$mediaRating->isDirty('description')) {
            return;
        }

        $superseded = trim((string) $mediaRating->getOriginal('description'));
        $replacement = trim((string) $mediaRating->description);

        // Withdrawing the review withdraws everything the reviewer said before it.
        if ($replacement === '') {
            $mediaRating->description_written_at = null;
            $this->forget($mediaRating);
            return;
        }

        if ($superseded === $replacement) {
            return;
        }

        $writtenAt = $mediaRating->getOriginal('description_written_at') ?? $mediaRating->getOriginal('created_at');
        $mediaRating->description_written_at = now();

        if ($superseded === '') {
            return;
        }

        // A version that stood only for a moment was a correction, not a change of mind.
        if ($writtenAt === null || $writtenAt->greaterThan(now()->subMinutes(MediaRatingRevision::MINIMUM_STANDING_MINUTES))) {
            return;
        }

        MediaRatingRevision::create([
            'rating_id' => $mediaRating->getKey(),
            'rating' => (float) $mediaRating->getOriginal('rating'),
            'description' => $superseded,
            'is_spoiler' => (bool) $mediaRating->getOriginal('is_spoiler'),
            'recommendation' => $mediaRating->getOriginal('recommendation'),
            'progress' => $mediaRating->getOriginal('progress'),
            'written_at' => $writtenAt,
        ]);

        $this->trim($mediaRating);
    }

    /**
     * Drops the versions that fall outside the kept window.
     *
     * @param MediaRating $mediaRating
     *
     * @return void
     */
    private function trim(MediaRating $mediaRating): void
    {
        $keptIDs = $mediaRating->revisions()
            ->limit(MediaRatingRevision::MAXIMUM_KEPT)
            ->pluck('id');

        MediaRatingRevision::where('rating_id', '=', $mediaRating->getKey())
            ->whereNotIn('id', $keptIDs)
            ->delete();
    }

    /**
     * Drops every version of the review.
     *
     * @param MediaRating $mediaRating
     *
     * @return void
     */
    private function forget(MediaRating $mediaRating): void
    {
        MediaRatingRevision::where('rating_id', '=', $mediaRating->getKey())
            ->delete();
    }
}
