<?php

namespace Laravel\Nova\Listeners;

use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaServiceProvider;
use Laravel\Nova\Tools\Dashboard;
use Laravel\Nova\Tools\ResourceManager;

class BootNova
{
    /**
     * Handle the event.
     *
     * @param  mixed  $event
     * @return void
     */
    public function handle($event)
    {
        app()->register(NovaServiceProvider::class);

        $this->registerDashboards();
        $this->registerTools();
        $this->registerResources();
    }

    /**
     * Register the dashboards used by Nova.
     *
     * @return void
     */
    protected function registerDashboards()
    {
        Nova::serving(function (ServingNova $event) {
            Nova::copyDefaultDashboardCards();
        });
    }

    /**
     * Boot the standard Nova resources.
     *
     * @return void
     */
    protected function registerResources()
    {
        Nova::resources([
            Nova::actionResource(),
        ]);
    }

    /**
     * Boot the standard Nova tools.
     *
     * @return void
     */
    protected function registerTools()
    {
        Nova::tools([
            new Dashboard,
            new ResourceManager,
        ]);
    }
}
