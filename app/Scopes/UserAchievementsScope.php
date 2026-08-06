<?php

namespace App\Scopes;

use App\Models\Achievement;
use App\Models\User;
use App\Models\UserAchievement;
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
        $builder->select([Achievement::TABLE_NAME . '.*', UserAchievement::TABLE_NAME . '.created_at as achieved_at', DB::raw('IF(' . UserAchievement::TABLE_NAME . '.id IS NOT NULL, true, false) as is_achieved')])
            ->leftJoin(UserAchievement::TABLE_NAME, function ($join) {
                $join->on(Achievement::TABLE_NAME . '.id', '=', UserAchievement::TABLE_NAME . '.achievement_id')
                    ->where(UserAchievement::TABLE_NAME . '.user_id', '=', $this->user->id);
            })
            ->where(function ($query) {
                $query->where(Achievement::TABLE_NAME . '.is_unlockable', true)
                    ->orWhereNotNull(UserAchievement::TABLE_NAME . '.id'); // Include if the user unlocked it, even if is_unlockable is false
            })
            ->withCasts([
                'achieved_at' => 'datetime',
            ]);
    }
}
