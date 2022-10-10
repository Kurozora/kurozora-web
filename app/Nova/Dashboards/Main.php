<?php

namespace App\Nova\Dashboards;

use App\Nova\Metrics\ActivityLogCount;
use App\Nova\Metrics\AnimeNSFWChart;
use App\Nova\Metrics\NewAnime;
use App\Nova\Metrics\NewEpisodes;
use App\Nova\Metrics\NewUsers;
use App\Nova\Metrics\NewViews;
use Laravel\Nova\Dashboards\Main as Dashboard;

class Main extends Dashboard
{
    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards(): array
    {
        return [
            new AnimeNSFWChart,
            new NewAnime,
            new NewEpisodes,
            new NewUsers,
            new NewViews,
            new ActivityLogCount,
        ];
    }
}
