<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SoftDeletingScopeBumpsUpdatedAt extends SoftDeletingScope
{
    /**
     * Extend the query builder with the needed functions.
     *
     * @param Builder<*> $builder
     * @return void
     */
    public function extend(Builder $builder): void
    {
        parent::extend($builder);

        $builder->onDelete(function (Builder $b) {
            $model = $b->getModel();
            $time = $model->freshTimestampString();

            return $b->update([
                $model->getDeletedAtColumn() => $time,
                $model->getUpdatedAtColumn() => $time,
            ]);
        });
    }
}
