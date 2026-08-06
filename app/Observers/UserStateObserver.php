<?php

namespace App\Observers;

use App\Models\User;
use Cog\Laravel\Love\Reaction\Models\Reaction;
use Illuminate\Database\Eloquent\Model;

class UserStateObserver
{
    /**
     * Bumps the owning user's state_version when the watched model is created.
     *
     * @param Model $model
     * @return void
     */
    public function created(Model $model): void
    {
        $this->bumpFor($model);
    }

    /**
     * Bumps the owning user's state_version when the watched model is updated.
     *
     * @param Model $model
     * @return void
     */
    public function updated(Model $model): void
    {
        $this->bumpFor($model);
    }

    /**
     * Bumps the owning user's state_version when the watched model is deleted.
     *
     * @param Model $model
     * @return void
     */
    public function deleted(Model $model): void
    {
        $this->bumpFor($model);
    }

    /**
     * Bumps the owning user's state_version when the watched model is restored.
     *
     * @param Model $model
     * @return void
     */
    public function restored(Model $model): void
    {
        $this->bumpFor($model);
    }

    /**
     * Resolves the affected user and bumps their state_version.
     *
     * @param Model $model
     * @return void
     */
    private function bumpFor(Model $model): void
    {
        $userId = $this->resolveUserId($model);

        if ($userId === null) {
            return;
        }

        $user = User::find($userId);
        $user?->bumpStateVersion();
    }

    /**
     * Returns the user id whose state_version should bump for the given watched model.
     *
     * @param Model $model
     * @return int|null
     */
    private function resolveUserId(Model $model): ?int
    {
        if ($model instanceof Reaction) {
            return User::query()
                ->where('love_reacter_id', '=', $model->getAttribute('reacter_id'))
                ->value('id');
        }

        return $model->getAttribute('user_id');
    }
}
