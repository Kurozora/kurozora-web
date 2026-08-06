<?php

namespace App\Traits\Livewire;

use App\Enums\ParentalGuideReaction;
use App\Models\MediaRating;

trait MediaRatingActions
{
    /**
     * The vote overrides keyed by media rating id.
     *
     * @var array $voteOverrides
     */
    public array $voteOverrides = [];

    /**
     * Toggles the (un)helpful vote on a review.
     *
     * @param int    $ratingID
     * @param string $direction
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
}
