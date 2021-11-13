<?php

namespace App\Scopes;

use App\Models\Character;
use App\Models\Person;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BornTodayScope implements Scope
{
    /**
     * @inheritDoc
     */
    public function apply(Builder $builder, Model $model)
    {
        if ($model instanceof Character) {
            $builder->where([
                ['birth_day', '=', today()->day],
                ['birth_month', '=', today()->month],
            ]);
        } else if ($model instanceof Person) {
            $builder->where('birthdate', 'like', '%'.today()->format('m-d'));
        }
    }
}
