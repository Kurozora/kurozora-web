<?php

namespace Laravel\Nova\Testing\Browser\Pages;

use Laravel\Dusk\Browser;

trait HasSearchableRelations
{
    use HasSearchable;

    /**
     * Search for the given value for a searchable relationship attribute.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @param  string  $attribute
     * @param  string  $search
     * @return void
     */
    public function searchRelation(Browser $browser, $attribute, $search)
    {
        $this->searchInput($browser, $attribute, $search);
    }

    /**
     * Select the currently highlighted searchable relation.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @param  string  $attribute
     * @return void
     */
    public function selectFirstRelation(Browser $browser, $attribute)
    {
        $this->selectFirstSearchResult($browser, $attribute);
    }

    /**
     * Select the currently highlighted searchable relation.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @param  string  $attribute
     * @return void
     */
    public function selectCurrentRelation(Browser $browser, $attribute)
    {
        $this->selectFirstRelation($browser, $attribute);
    }

    /**
     * Select the currently highlighted searchable relation.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @param  string  $attribute
     * @return void
     */
    public function cancelSelectingRelation(Browser $browser, $attribute)
    {
        $this->cancelSelectingSearchResult($browser, $attribute);
    }

    /**
     * Search and select the currently highlighted searchable relation.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @param  string  $attribute
     * @param  string  $search
     * @return void
     */
    public function searchAndSelectFirstRelation(Browser $browser, $attribute, $search)
    {
        $this->searchAndSelectFirstResult($browser, $attribute, $search);
    }

    /**
     * Indicate that trashed relations should be included in the search results.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @param  string  $resourceName
     * @return void
     */
    public function withTrashedRelation(Browser $browser, $resourceName)
    {
        $browser->click('')->with(
            "@{$resourceName}-with-trashed-checkbox",
            function (Browser $browser) {
                $browser->check('input[type="checkbox"]')->pause(250);
            }
        );
    }

    /**
     * Indicate that trashed relations should not be included in the search results.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @param  string  $resourceName
     * @return void
     */
    public function withoutTrashedRelation(Browser $browser, $resourceName)
    {
        $browser->uncheck('[dusk="'.$resourceName.'-with-trashed-checkbox"] input[type="checkbox"]')->pause(250);
    }
}
