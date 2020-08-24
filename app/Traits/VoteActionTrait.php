<?php

namespace App\Traits;

use App\Enums\ForumsVoteType;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use Cog\Contracts\Love\Reactable\Models\Reactable;
use Cog\Contracts\Love\Reacterable\Models\Reacterable;

trait VoteActionTrait {
    /**
     * Gets the current user's vote value.
     *
     * -1   = disliked
     * 0    = neutral (no vote)
     * 1    = liked
     *
     * @return int the current user's vote value.
     * @throws InvalidEnumKeyException
     */
    public function getCurrentVoteValue(): int
    {
        /** @var Reacterable $reactant */
        $reactant = $this;
        $reacter = $reactant->viaLoveReacter();

        foreach($reacter->getReactions() as $reaction)
            return ForumsVoteType::fromKey($reaction->type->name)->value;

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
        /** @var Reacterable $reactant */
        $reactant = $this;
        $reacter = $reactant->viaLoveReacter();
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
