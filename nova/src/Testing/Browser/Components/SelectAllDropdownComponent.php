<?php

namespace Laravel\Nova\Testing\Browser\Components;

use Laravel\Dusk\Browser;

class SelectAllDropdownComponent extends Component
{
    /**
     * Get the root selector for the component.
     *
     * @return string
     */
    public function selector()
    {
        return '@select-all-dropdown';
    }

    /**
     * Assert that the checkbox is checked.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @return void
     */
    public function assertCheckboxIsChecked(Browser $browser)
    {
        $browser->assertPresent('span.fake-checkbox.fake-checkbox-checked');
    }

    /**
     * Assert that the checkbox is not checked.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @return void
     */
    public function assertCheckboxIsNotChecked(Browser $browser)
    {
        $browser->assertPresent('span.fake-checkbox')
            ->assertNotPresent('span.fake-checkbox.fake-checkbox-checked')
            ->assertNotPresent('span.fake-checkbox.fake-checkbox-indeterminate');
    }

    /**
     * Assert that the checkbox is indeterminate.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @return void
     */
    public function assertCheckboxIsIndeterminate(Browser $browser)
    {
        $browser->assertPresent('span.fake-checkbox.fake-checkbox-indeterminate');
    }

    /**
     * Assert select all the the resources on current page is checked.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @return void
     */
    public function assertSelectAllOnCurrentPageChecked(Browser $browser)
    {
        $browser->click('')
            ->elsewhereWhenAvailable('[dusk="select-all-button"]', function ($browser) {
                $browser->assertChecked('input[type="checkbox"]');
            })
            ->closeCurrentDropdown();
    }

    /**
     * Assert select all the the resources on current page isn't checked.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @return void
     */
    public function assertSelectAllOnCurrentPageNotChecked(Browser $browser)
    {
        $browser->click('')
            ->elsewhereWhenAvailable('[dusk="select-all-button"]', function ($browser) {
                $browser->assertNotChecked('input[type="checkbox"]');
            })
            ->closeCurrentDropdown();
    }

    /**
     * Assert select all the matching resources is checked.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @return void
     */
    public function assertSelectAllMatchingChecked(Browser $browser)
    {
        $browser->click('')
            ->elsewhereWhenAvailable('[dusk="select-all-matching-button"]', function ($browser) {
                $browser->assertChecked('input[type="checkbox"]');
            })
            ->closeCurrentDropdown();
    }

    /**
     * Assert select all the matching resources isn't checked.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @return void
     */
    public function assertSelectAllMatchingNotChecked(Browser $browser)
    {
        $browser->click('')
            ->elsewhereWhenAvailable('[dusk="select-all-matching-button"]', function ($browser) {
                $browser->assertNotChecked('input[type="checkbox"]');
            })
            ->closeCurrentDropdown();
    }

    /**
     * Assert on the matching total matching count text.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @param  int  $count
     * @return void
     */
    public function assertSelectAllMatchingCount(Browser $browser, $count)
    {
        $browser->click('')
            ->elsewhereWhenAvailable('@select-all-matching-button', function (Browser $browser) use ($count) {
                $browser->assertSeeIn('span:nth-child(2)', $count);
            })
            ->closeCurrentDropdown();
    }

    /**
     * Select all the the resources on current page.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @return void
     */
    public function selectAllOnCurrentPage(Browser $browser)
    {
        $browser->click('')
            ->elsewhereWhenAvailable('[dusk="select-all-button"]', function ($browser) {
                $browser->check('input[type="checkbox"]');
            })
            ->pause(250)
            ->closeCurrentDropdown();
    }

    /**
     * Un-select all the the resources on current page.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @return void
     */
    public function unselectAllOnCurrentPage(Browser $browser)
    {
        $browser->click('')
            ->elsewhereWhenAvailable('[dusk="select-all-button"]', function ($browser) {
                $browser->uncheck('input[type="checkbox"]');
            })
            ->pause(250)
            ->closeCurrentDropdown();
    }

    /**
     * Select all the matching resources.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @return void
     */
    public function selectAllMatching(Browser $browser)
    {
        $browser->click('')
            ->elsewhereWhenAvailable('[dusk="select-all-matching-button"]', function ($browser) {
                $browser->check('input[type="checkbox"]');
            })
            ->pause(250)
            ->closeCurrentDropdown();
    }

    /**
     * Un-select all the matching resources.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @return void
     */
    public function unselectAllMatching(Browser $browser)
    {
        $browser->click('')
            ->elsewhereWhenAvailable('[dusk="select-all-matching-button"]', function ($browser) {
                $browser->uncheck('input[type="checkbox"]');
            })
            ->pause(250)
            ->closeCurrentDropdown();
    }

    /**
     * Assert that the browser page contains the component.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @return void
     *
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function assert(Browser $browser)
    {
        tap($this->selector(), function ($selector) use ($browser) {
            $browser->scrollIntoView($selector);
        });
    }
}
