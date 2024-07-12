<?php

namespace App\Scopes;

use App\Enums\UserLibraryStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class IgnoreListScope implements Scope
{
    /**
     * All extensions to be added to the builder.
     *
     * @var string[]
     */
    protected array $extensions = ['WithIgnoreList', 'WithoutIgnoreList'];

    /**
     * @inheritDoc
     */
    public function apply(Builder $builder, Model $model): void
    {
        if ($user = auth()->user()) {
            $builder->whereDoesntHave('library', function ($query) use ($user, $model) {
                $query->where('status', '=', UserLibraryStatus::Ignored)
                    ->where('user_id', '=', $user->id);
            });
        }
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param Builder $builder
     * @return void
     */
    public function extend(Builder $builder): void
    {
        foreach ($this->extensions as $extension) {
            $this->{"add$extension"}($builder);
        }
    }

    /**
     * Get the "ignore list" column for the builder.
     *
     * @param Builder $builder
     * @return string
     */
    protected function getIgnoreListColumn(Builder $builder): string
    {
        if (count($builder->getQuery()->joins) > 0) {
            return $builder->getModel()->getQualifiedIgnoreListColumn();
        }

        return $builder->getModel()->getIgnoreListColumn();
    }

    /**
     * Add the with-ignore-list extension to the builder.
     *
     * @param Builder $builder
     * @return void
     */
    protected function addWithIgnoreList(Builder $builder): void
    {
        $builder->macro('withIgnoreList', function (Builder $builder, $withIgnoreList = true) {
            if (!$withIgnoreList) {
                return $builder->withoutIgnoreList();
            }

            return $builder->withoutGlobalScope($this);
        });
    }

    /**
     * Add the without-ignore-list extension to the builder.
     *
     * @param Builder $builder
     * @return void
     */
    protected function addWithoutIgnoreList(Builder $builder): void
    {
        $builder->macro('withoutIgnoreList', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
