<?php

namespace Laravel\Nova\Testing\Browser\Pages;

use Laravel\Dusk\Browser;

trait HasSearchable
{
    /**
     * Search for the given value for a searchable field attribute.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @param  string  $attribute
     * @param  string  $search
     * @return void
     */
    public function searchInput(Browser $browser, $attribute, $search)
    {
        $browser->click('[dusk="'.$attribute.'-search-input"]')
                ->pause(100)
                ->type('[dusk="'.$attribute.'-search-input"] input', $search);
    }

    /**
     * Select the searchable field by result index.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @param  string  $attribute
     * @param  int  $resultIndex
     * @return void
     */
    public function selectSearchResult(Browser $browser, $attribute, $resultIndex)
    {
        $browser->click('[dusk="'.$attribute.'-search-input-result-'.$resultIndex.'"]')->pause(150);
    }

    /**
     * Select the currently highlighted searchable field.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @param  string  $attribute
     * @return void
     */
    public function selectFirstSearchResult(Browser $browser, $attribute)
    {
        $this->selectSearchResult($browser, $attribute, 0);
    }

    /**
     * Select the currently highlighted searchable field.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @param  string  $attribute
     * @return void
     */
    public function cancelSelectingSearchResult(Browser $browser, $attribute)
    {
        $browser->keys('[dusk="'.$attribute.'-search-input"] input', '{escape}')->pause(150);
    }

    /**
     * Search and select the searchable field by result index.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @param  string  $attribute
     * @param  string  $search
     * @param  int  $resultIndex
     * @return void
     */
    public function searchAndSelectResult(Browser $browser, $attribute, $search, $resultIndex)
    {
        $this->searchInput($browser, $attribute, $search);

        $browser->pause(1500);

        $this->selectSearchResult($browser, $attribute, $resultIndex);
    }

    /**
     * Search and select the currently highlighted searchable field.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @param  string  $attribute
     * @param  string  $search
     * @return void
     */
    public function searchAndSelectFirstResult(Browser $browser, $attribute, $search)
    {
        $this->searchAndSelectResult($browser, $attribute, $search, 0);
    }
}
