<?php

namespace Laravel\Nova\Testing\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Nova\Nova;

class Index extends Page
{
    public $resourceName;

    /**
     * Create a new page instance.
     *
     * @param  string  $resourceName
     * @return void
     */
    public function __construct($resourceName)
    {
        $this->resourceName = $resourceName;
    }

    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return Nova::path().'/resources/'.$this->resourceName;
    }

    /**
     * Assert that the browser is on the page.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @return void
     */
    public function assert(Browser $browser)
    {
        //
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
