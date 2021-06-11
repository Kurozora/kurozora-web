<?php

namespace Laravel\Nova\Testing\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Nova\Nova;

class Dashboard extends Page
{
    public $dashboardName;

    /**
     * Create a new page instance.
     *
     * @param  string  $dashboardName
     * @return void
     */
    public function __construct($dashboardName = 'main')
    {
        $this->dashboardName = $dashboardName;
    }

    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return Nova::path().'/dashboards/'.$this->dashboardName;
    }

    /**
     * Assert that the browser is on the page.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @return void
     */
    public function assert(Browser $browser)
    {
        $browser->pause(500);
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array
     */
    public function elements()
    {
        return [];
    }
}
