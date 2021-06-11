<?php

namespace Laravel\Nova\Testing\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Nova\Nova;

class Lens extends Index
{
    public $lens;

    /**
     * Create a new page instance.
     *
     * @param  string  $resourceName
     * @param  string  $lens
     * @return void
     */
    public function __construct($resourceName, $lens)
    {
        $this->lens = $lens;
        $this->resourceName = $resourceName;
    }

    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return Nova::path().'/resources/'.$this->resourceName.'/lens/'.$this->lens;
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
}
