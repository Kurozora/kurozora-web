<?php

namespace Laravel\Nova\Testing\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Nova\Nova;

class Update extends Page
{
    use HasSearchableRelations;

    public $resourceName;
    public $resourceId;
    public $queryParams;

    /**
     * Create a new page instance.
     *
     * @param  string  $resourceName
     * @param  int  $resourceId
     * @param  array  $queryParams
     * @return void
     */
    public function __construct($resourceName, $resourceId, $queryParams = [])
    {
        $this->resourceName = $resourceName;
        $this->resourceId = $resourceId;
        $this->queryParams = $queryParams;
    }

    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        $url = Nova::path().'/resources/'.$this->resourceName.'/'.$this->resourceId.'/edit';

        if ($this->queryParams) {
            $url .= '?'.http_build_query($this->queryParams);
        }

        return $url;
    }

    /**
     * Run the inline create relation.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @param  string  $uriKey
     * @param  callable  $fieldCallback
     * @return void
     *
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function runInlineCreate(Browser $browser, $uriKey, callable $fieldCallback)
    {
        $browser->whenAvailable("@{$uriKey}-inline-create", function ($browser) use ($fieldCallback) {
            $browser->click('')
                ->elsewhere('', function ($browser) use ($fieldCallback) {
                    $browser->whenAvailable('.modal', function ($browser) use ($fieldCallback) {
                        $fieldCallback($browser);

                        $browser->create()->pause(250);
                    });
                });
        });
    }

    /**
     * Click the update button.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @return void
     *
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function update(Browser $browser)
    {
        $browser->waitFor('@update-button')
                ->click('@update-button')
                ->pause(500);
    }

    /**
     * Click the update and continue editing button.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @return void
     *
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function updateAndContinueEditing(Browser $browser)
    {
        $browser->waitFor('@update-and-continue-editing-button')
                ->click('@update-and-continue-editing-button')
                ->pause(500);
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
