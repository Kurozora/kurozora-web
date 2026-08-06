<?php

namespace App\Traits;

use App\Enums\ParentalGuideReaction;
use Cog\Contracts\Love\Reactable\Models\Reactable;
use Illuminate\Database\Eloquent\Model;

trait HelpfulnessActionTrait
{
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

        if ($reaction->is(ParentalGuideReaction::Helpful)) {
            if ($hasUnhelpful) {
                $reacter->unreactTo($reactable, ParentalGuideReaction::Unhelpful()->description);
            }

            if (!$hasHelpful) {
                $reacter->reactTo($reactable, ParentalGuideReaction::Helpful()->description);
            }
        } else {
            if ($hasHelpful) {
                $reacter->unreactTo($reactable, ParentalGuideReaction::Helpful()->description);
            }

            if (!$hasUnhelpful) {
                $reacter->reactTo($reactable, ParentalGuideReaction::Unhelpful()->description);
            }
        }

        return $reaction;
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
