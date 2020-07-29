<?php

namespace App\Traits;

use App\Enums\VoteType;
use Cog\Contracts\Love\Reactable\Models\Reactable;
use Cog\Contracts\Love\Reacterable\Models\Reacterable;

trait VoteActionTrait {
    /**
     * Gets the current vote of the given Reactable object.
     *
     * -1   = disliked
     * 0    = neutral (no vote)
     * 1    = liked
     *
     * @param Reactable $reactable The Raectable object for which the current vote value should be returned.
     *
     * @return int the vote value of the given Reactable object.
     *
     * @throws \BenSampo\Enum\Exceptions\InvalidEnumKeyException
     */
    public function getCurrentVoteValueFor(Reactable $reactable): int {
        /** @var Reacterable $reactant */
        $reactant = $this;
        $reacter = $reactant->viaLoveReacter();

        foreach($reacter->getReactions() as $reaction) {
            return VoteType::fromKey($reaction->type->name)->value;
        }

        return 0;
    }

    /**
     * Toggles the vote between 'Like' and 'Dislike' or removes the vote if it already exists.
     *
     * @param Reactable $reactable The object to react on.
     * @param VoteType $voteType The vote to be applied on the given Reactable object.
     *
     * @return int the current state of the VoteType on the given Reactable object.
     *
     * @throws \BenSampo\Enum\Exceptions\InvalidEnumKeyException
     */
    public function toggleVote(Reactable $reactable, VoteType $voteType): int {
        /** @var Reacterable $reactant */
        $reactant = $this;
        $reacter = $reactant->viaLoveReacter();
        $currentVoteValue = $this->getCurrentVoteValueFor($reactable);
        $voteTypeDescription = (string) $voteType->description;
        $nextVoteTypeDescription = (string) $voteType->next()->description;

        if ($reacter->hasReactedTo($reactable, $nextVoteTypeDescription)) {
            $reacter->unreactTo($reactable, $nextVoteTypeDescription);
        }

        if ($currentVoteValue == $voteType->value) {
            $reacter->unreactTo($reactable, $voteTypeDescription);
            return 0;
        }

        switch ($voteType->value) {
            case VoteType::Like:
                $reacter->reactTo($reactable, $voteTypeDescription);
                return 1;
            default:
                $reacter->reactTo($reactable, $voteTypeDescription);
                return -1;
        }
    }
}
