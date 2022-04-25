<?php

namespace App\Nova\Tools;

use Gate;
use Vyuldashev\NovaPermission\NovaPermissionTool as BaseNovaPermissionTool;

class NovaPermissionTool extends BaseNovaPermissionTool
{
    /**
     * Perform any tasks that need to happen when the tool is booted.
     *
     * @return void
     */
    public function boot(): void
    {
        Gate::policy(config('permission.models.permission'), $this->permissionPolicy);
        Gate::policy(config('permission.models.role'), $this->rolePolicy);
    }
}
