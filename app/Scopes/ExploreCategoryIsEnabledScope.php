<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ExploreCategoryIsEnabledScope implements Scope
{
    /**
     * @inheritDoc
     */
    public function apply(Builder $builder, Model $model)
    {
        $onlyEnabled = request()?->attributes->get('exploreOnlyEnabled', true) ?? true;

        if (!$onlyEnabled) {
            return;
        }

        $builder->where('is_enabled', '=', true);
    }
}
