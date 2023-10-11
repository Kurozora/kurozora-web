<?php

namespace App\Traits;

use App\Enums\FeedVoteType;
use Cog\Contracts\Love\Reactable\Models\Reactable;

trait HeartActionTrait {
    /**
     * Gets the user's current heart value for the given reactable object.
     *
     * 0    = neutral (no vote)
     * 1    = hearted
     *
     * @param Reactable $reactable The object whose current reaction to get.
     * @return int the current user's heart value.
     */
    public function getCurrentHeartValueFor(Reactable $reactable): int
    {
        $reacterable = $this;
        $hasHearted = $reacterable->viaLoveReacter()->hasReactedTo($reactable, FeedVoteType::Heart()->description);

        return $hasHearted ? FeedVoteType::Heart : FeedVoteType::UnHeart;
    }

    /**
     * Toggles the vote between 'Heart' and 'UnHeart'.
     *
     * @param Reactable $reactable The object to react on.
     * @return int the current state of the VoteType on the given Reactable object.
     */
    public function toggleHeart(Reactable $reactable): int
    {
        $reacterable = $this;
        $reacter = $reacterable->viaLoveReacter();

        if ($reacter->hasReactedTo($reactable)) {
            $reacter->unreactTo($reactable, FeedVoteType::Heart()->description);
            return 0;
        }

        $reacter->reactTo($reactable, FeedVoteType::Heart()->description);
        return 1;
    }
}
