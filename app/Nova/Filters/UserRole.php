<?php

namespace App\Nova\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\BooleanFilter;
use Spatie\Permission\Models\Role;

class UserRole extends BooleanFilter
{
    /**
     * Apply the filter to the given query.
     *
     * @param Request $request
     * @param Builder  $query
     * @param mixed  $value
     * @return Builder
     */
    public function apply(Request $request, $query, $value): Builder
    {
        foreach($value as $roleName => $enabled) {
            if (!$enabled) {
                continue;
            }

            $query->role($roleName);
        }

        return $query;
    }

    /**
     * Get the filter's available options.
     *
     * @param Request $request
     * @return array
     */
    public function options(Request $request): array
    {
        $rolesArray = [];

        foreach(Role::all() as $role) {
            $rolesArray[ucfirst($role->name)] = $role->name;
        }

        return $rolesArray;
    }
}
