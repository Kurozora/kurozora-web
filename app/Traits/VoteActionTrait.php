<?php

namespace App\Traits;

use App\Enums\ForumsVoteType;
use Cog\Contracts\Love\Reactable\Models\Reactable;
use Cog\Contracts\Love\Reacterable\Models\Reacterable;

trait VoteActionTrait {
    /**
     * Gets the user's current vote value for the given reactable object.
     *
     * -1   = disliked
     * 0    = neutral (no vote)
     * 1    = liked
     *
     * @param Reactable $reactable The object whose vote value to get.
     * @return int the current user's vote value.
     */
    public function getCurrentVoteValueFor(Reactable $reactable): int
    {
        /** @var Reacterable $reacterable */
        $reacterable = $this;

        if ($reacterable->viaLoveReacter()->hasReactedTo($reactable, ForumsVoteType::Like()->description))
            return ForumsVoteType::Like;
        else if ($reacterable->viaLoveReacter()->hasReactedTo($reactable, ForumsVoteType::Dislike()->description))
            return ForumsVoteType::Dislike;

        return 0;
    }

    /**
     * Toggles the vote between 'Like' and 'Dislike' or removes the vote if it already exists.
     *
     * @param Reactable $reactable The object to react on.
     * @param ForumsVoteType $voteType The vote to be applied on the given Reactable object.
     * @return int the current state of the VoteType on the given Reactable object.
     */
    public function toggleVote(Reactable $reactable, ForumsVoteType $voteType): int
    {
        /** @var Reacterable $reacterable */
        $reacterable = $this;
        $reacter = $reacterable->viaLoveReacter();
        $voteTypeDescription = (string) $voteType->description;
        $nextVoteTypeDescription = (string) $voteType->next()->description;

        if ($reacter->hasReactedTo($reactable, $nextVoteTypeDescription)) {
            $reacter->unreactTo($reactable, $nextVoteTypeDescription);
        }

        if ($reacter->hasReactedTo($reactable, $voteTypeDescription)) {
            $reacter->unreactTo($reactable, $voteTypeDescription);

            return 0;
        }

        switch ($voteType->value) {
            case ForumsVoteType::Like:
                $reacter->reactTo($reactable, $voteTypeDescription);
                return 1;
            default:
                $reacter->reactTo($reactable, $voteTypeDescription);
                return -1;
        }
    }
}
