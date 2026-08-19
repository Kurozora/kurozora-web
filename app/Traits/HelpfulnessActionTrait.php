<?php

namespace App\Traits;

use App\Enums\ParentalGuideReaction;
use Cog\Contracts\Love\Reactable\Models\Reactable;
use Illuminate\Database\Eloquent\Model;

trait HelpfulnessActionTrait
{
    // Weight boundary
    const float MAX_HELPFULNESS_WEIGHT = 5.0;

    /**
     * Toggle the user's helpful reaction on the given reactable.
     *
     * @param Reactable                  $reactable The reactable to react on.
     * @param ParentalGuideReaction|null $reaction  The reaction to apply, or `null` to clear.
     *
     * @return ParentalGuideReaction|null The reaction now in effect, or `null` if cleared.
     */
    public function setHelpfulness(Reactable $reactable, ?ParentalGuideReaction $reaction): ?ParentalGuideReaction
    {
        $this->loadMissing('loveReacter');

        if ($reactable instanceof Model) {
            $reactable->loadMissing('loveReactant');
        }

        $reacter = $this->viaLoveReacter();

        $hasHelpful = $reacter->hasReactedTo($reactable, ParentalGuideReaction::Helpful()->description);
        $hasUnhelpful = $reacter->hasReactedTo($reactable, ParentalGuideReaction::Unhelpful()->description);

        if ($reaction === null) {
            if ($hasHelpful) {
                $reacter->unreactTo($reactable, ParentalGuideReaction::Helpful()->description);
            }

            if ($hasUnhelpful) {
                $reacter->unreactTo($reactable, ParentalGuideReaction::Unhelpful()->description);
            }

            return null;
        }

        $rate = $this->helpfulnessVoteWeight();

        if ($reaction->is(ParentalGuideReaction::Helpful)) {
            if ($hasUnhelpful) {
                $reacter->unreactTo($reactable, ParentalGuideReaction::Unhelpful()->description);
            }

            if (!$hasHelpful) {
                $reacter->reactTo($reactable, ParentalGuideReaction::Helpful()->description, $rate);
            }
        } else {
            if ($hasHelpful) {
                $reacter->unreactTo($reactable, ParentalGuideReaction::Helpful()->description);
            }

            if (!$hasUnhelpful) {
                $reacter->reactTo($reactable, ParentalGuideReaction::Unhelpful()->description, $rate);
            }
        }

        return $reaction;
    }

    /**
     * The weight the user's vote carries.
     *
     * @return float
     */
    public function helpfulnessVoteWeight(): float
    {
        return self::helpfulnessWeightFor((int) $this->reputation_count);
    }

    /**
     * The vote weight earned by the given reputation.
     *
     * @param int $reputationCount
     *
     * @return float
     */
    public static function helpfulnessWeightFor(int $reputationCount): float
    {
        return min(self::MAX_HELPFULNESS_WEIGHT, 1.0 + log(1 + max(0, $reputationCount), 2) / 2);
    }

    /**
     * Returns the user's current helpful / unhelpful reaction on the reactable, if any.
     *
     * @param Reactable $reactable The reactable to inspect.
     *
     * @return ParentalGuideReaction|null
     */
    public function getHelpfulnessFor(Reactable $reactable): ?ParentalGuideReaction
    {
        $this->loadMissing('loveReacter');

        if ($reactable instanceof Model) {
            $reactable->loadMissing('loveReactant');
        }

        $reacter = $this->viaLoveReacter();

        if ($reacter->hasReactedTo($reactable, ParentalGuideReaction::Helpful()->description)) {
            return ParentalGuideReaction::Helpful();
        }

        if ($reacter->hasReactedTo($reactable, ParentalGuideReaction::Unhelpful()->description)) {
            return ParentalGuideReaction::Unhelpful();
        }

        return null;
    }
}
