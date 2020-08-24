<?php

namespace App\Traits;

use App\Enums\FeedVoteType;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use Cog\Contracts\Love\Reactable\Models\Reactable;
use Cog\Contracts\Love\Reacterable\Models\Reacterable;

trait HeartActionTrait {
    /**
     * Gets the current user's heart value.
     *
     * 0    = neutral (no vote)
     * 1    = hearted
     *
     * @return int the current user's heart value.
     * @throws InvalidEnumKeyException
     */
    public function getCurrentHeartValue(): int
    {
        /** @var Reacterable $reactant */
        $reactant = $this;
        $reacter = $reactant->viaLoveReacter();

        foreach($reacter->getReactions() as $reaction)
            return FeedVoteType::fromKey($reaction->type->name)->value;

        return 0;
    }

    /**
     * Toggles the vote between 'Heart' and 'UnHeart'.
     *
     * @param Reactable $reactable The object to react on.
     * @return int the current state of the VoteType on the given Reactable object.
     */
    public function toggleHeart(Reactable $reactable): int
    {
        /** @var Reacterable $reactant */
        $reactant = $this;
        $reacter = $reactant->viaLoveReacter();

        if ($reacter->hasReactedTo($reactable)) {
            $reacter->unreactTo($reactable, FeedVoteType::Heart()->description);
            return 0;
        }

        $reacter->reactTo($reactable, FeedVoteType::Heart()->description);
        return 1;
    }
}
