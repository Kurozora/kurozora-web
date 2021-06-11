<?php

namespace Laravel\Nova\Testing\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Nova\Nova;

class Create extends Page
{
    use HasSearchableRelations;

    public $resourceName;
    public $queryParams;

    /**
     * Create a new page instance.
     *
     * @param  string  $resourceName
     * @param  array  $queryParams
     * @return void
     */
    public function __construct($resourceName, $queryParams = [])
    {
        $this->resourceName = $resourceName;
        $this->queryParams = $queryParams;
    }

    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        $url = Nova::path().'/resources/'.$this->resourceName.'/new';

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
     * Click the create button.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @return void
     */
    public function create(Browser $browser)
    {
        $browser->click('@create-button')->pause(500);
    }

    /**
     * Click the create and add another button.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @return void
     */
    public function createAndAddAnother(Browser $browser)
    {
        $browser->click('@create-and-add-another-button')->pause(500);
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
     * Assert that there are no search results.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @param  string  $resourceName
     * @return void
     */
    public function assertNoRelationSearchResults(Browser $browser, $resourceName)
    {
        $browser->assertMissing('@'.$resourceName.'-search-input-result-0');
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
