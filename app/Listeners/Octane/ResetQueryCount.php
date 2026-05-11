<?php

namespace App\Listeners\Octane;

use App\Providers\AppServiceProvider;

class ResetQueryCount
{
    /**
     * Reset the per-request query counter when a new Octane request begins.
     *
     * @param object $event
     * @return void
     */
    public function handle(object $event): void
    {
        AppServiceProvider::$queryCount = 0;
    }
}
