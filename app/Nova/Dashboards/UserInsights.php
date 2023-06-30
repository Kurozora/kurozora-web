<?php

namespace App\Nova\Dashboards;

use App\Nova\Metrics\ActivityLogCount;
use App\Nova\Metrics\NewUsers;
use App\Nova\Metrics\ProUsers;
use App\Nova\Metrics\SubscribedUsers;
use App\Nova\Metrics\UsersPerDay;
use App\Nova\Metrics\UsersPerPlan;
use Laravel\Nova\Dashboards\Main as Dashboard;

class UserInsights extends Dashboard
{
    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards(): array
    {
        return [
            NewUsers::make(),
            UsersPerDay::make(),
            UsersPerPlan::make(),
            ProUsers::make(),
            SubscribedUsers::make(),
            ActivityLogCount::make(),
        ];
    }

    /**
     * Get the displayable name of the dashboard.
     *
     * @return string
     */
    public function name(): string
    {
        return 'User Insights';
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey(): string
    {
        return 'user-insights';
    }
}
