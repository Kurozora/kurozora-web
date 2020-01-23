<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\BooleanFilter;
use Spatie\Permission\Models\Role;

class UserRole extends BooleanFilter
{
    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        foreach($value as $roleName => $enabled) {
            if(!$enabled) continue;

            $query->role($roleName);
        }

        return $query;
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        $rolesArray = [];

        foreach(Role::all() as $role)
            $rolesArray[ucfirst($role->name)] = $role->name;

        return $rolesArray;
    }
}
