<?php

namespace App\Scopes;

use App\Models\Badge;
use App\Models\User;
use App\Models\UserBadge;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class UserAchievementsScope implements Scope
{
    /**
     * The object containing the user data.
     *
     * @var User
     */
    public User $user;

    /**
     * Create a new instance of `UserAchievementsScope`.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @inheritDoc
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->select([Badge::TABLE_NAME . '.*', DB::raw('IF(' . UserBadge::TABLE_NAME . '.id IS NOT NULL, true, false) as is_achieved')])
            ->leftJoin(UserBadge::TABLE_NAME, function ($join) {
                $join->on(Badge::TABLE_NAME . '.id', '=', UserBadge::TABLE_NAME . '.badge_id')
                    ->where(UserBadge::TABLE_NAME . '.user_id', '=', $this->user->id);
            })
            ->where(function ($query) {
                $query->where(Badge::TABLE_NAME . '.is_unlockable', true)
                    ->orWhereNotNull(UserBadge::TABLE_NAME . '.id'); // Include if the user unlocked it, even if is_unlockable is false
            });
    }
}
