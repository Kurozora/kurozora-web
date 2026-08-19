<?php

namespace App\Traits\Livewire;

use App\Enums\ParentalGuideReaction;
use App\Models\MediaRating;

trait MediaRatingActions
{
    /**
     * Optimistic vote overrides keyed by media rating id.
     *
     * @var array<int, array{helpful: bool|null, helpfulCount: int, unhelpfulCount: int}> $voteOverrides
     */
    public array $voteOverrides = [];

    /**
     * Toggles the (un)helpful vote on a review, applying an optimistic count update so the UI
     * reflects the change immediately.
     *
     * @param int    $ratingID
     * @param string $direction Either `helpful` or `unhelpful`.
     *
     * @return void
     */
    public function voteOnReview(int $ratingID, string $direction): void
    {
        $user = auth()->user();

        if ($user === null) {
            $this->redirect(route('sign-in'));
            return;
        }

        $mediaRating = MediaRating::withoutGlobalScopes()
            ->find($ratingID);

        if ($mediaRating === null) {
            return;
        }

        // Ratings created before reactions shipped aren't registered as reactants yet.
        if ($mediaRating->isNotRegisteredAsLoveReactant()) {
            $mediaRating->registerAsLoveReactant();
            $mediaRating->refresh();
        }

        $mediaRating->load(MediaRating::lockupEagerLoads($user));
        $current = $user->getHelpfulnessFor($mediaRating);
        $oldHelpful = $current === null ? null : $current->is(ParentalGuideReaction::Helpful());
        $tappedHelpful = match ($direction) {
            'helpful' => true,
            'unhelpful' => false,
            default => null,
        };

        $predicted = ($oldHelpful === $tappedHelpful) ? null : $tappedHelpful;

        $existingOverride = $this->voteOverrides[$ratingID] ?? null;
        $helpfulCount = $existingOverride['helpfulCount'] ?? $mediaRating->helpful_count;
        $unhelpfulCount = $existingOverride['unhelpfulCount'] ?? $mediaRating->unhelpful_count;

        if ($oldHelpful !== $predicted) {
            if ($oldHelpful === true) {
                $helpfulCount = max(0, $helpfulCount - 1);
            } elseif ($oldHelpful === false) {
                $unhelpfulCount = max(0, $unhelpfulCount - 1);
            }

            if ($predicted === true) {
                $helpfulCount++;
            } elseif ($predicted === false) {
                $unhelpfulCount++;
            }
        }

        $this->voteOverrides[$ratingID] = [
            'helpful' => $predicted,
            'helpfulCount' => $helpfulCount,
            'unhelpfulCount' => $unhelpfulCount,
        ];

        $reaction = match ($predicted) {
            true => ParentalGuideReaction::Helpful(),
            false => ParentalGuideReaction::Unhelpful(),
            default => null,
        };

        $user->setHelpfulness($mediaRating, $reaction);
    }

    /**
     * Toggles whether a review holds the item's Editor's Choice slot.
     *
     * @param int $ratingID
     *
     * @return void
     */
    public function elevateReview(int $ratingID): void
    {
        $user = auth()->user();

        if ($user === null || !$user->can('elevateMediaRating')) {
            return;
        }

        $mediaRating = MediaRating::withoutGlobalScopes()
            ->find($ratingID);

        if ($mediaRating === null || trim((string) $mediaRating->description) === '') {
            return;
        }

        if ($mediaRating->is_elevated) {
            $mediaRating->update([
                'is_elevated' => false,
                'elevated_at' => null,
                'elevated_by_user_id' => null,
            ]);

            return;
        }

        // The slot holds one review, so the review that held it steps down.
        MediaRating::withoutGlobalScopes()
            ->where('model_type', '=', $mediaRating->model_type)
            ->where('model_id', '=', $mediaRating->model_id)
            ->where('is_elevated', '=', true)
            ->update([
                'is_elevated' => false,
                'elevated_at' => null,
                'elevated_by_user_id' => null,
            ]);

        $mediaRating->update([
            'is_elevated' => true,
            'elevated_at' => now(),
            'elevated_by_user_id' => $user->id,
        ]);
    }
}
