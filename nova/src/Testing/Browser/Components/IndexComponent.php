<?php

namespace Laravel\Nova\Testing\Browser\Components;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Component as BaseComponent;

class IndexComponent extends BaseComponent
{
    public $resourceName;

    public $viaRelationship;

    /**
     * Create a new component instance.
     *
     * @param  string  $resourceName
     * @param  string|null  $viaRelationship
     * @return void
     */
    public function __construct($resourceName, $viaRelationship = null)
    {
        $this->resourceName = $resourceName;

        if (! is_null($viaRelationship) && $resourceName !== $viaRelationship) {
            $this->viaRelationship = $viaRelationship;
        }
    }

    /**
     * Get the root selector for the component.
     *
     * @return string
     */
    public function selector()
    {
        $selector = '[dusk="'.$this->resourceName.'-index-component"]';

        return sprintf(
           (! is_null($this->viaRelationship) ? '%s[data-relationship="%s"]' : '%s'), $selector, $this->viaRelationship
        );
    }

    /**
     * Wait for table to be ready.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @param  int|null  $seconds
     * @return void
     *
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function waitForTable(Browser $browser, $seconds = null)
    {
        $browser->waitFor('table[data-testid="resource-table"]', $seconds);
    }

    /**
     * Search for the given string.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @param  string  $search
     * @return void
     */
    public function searchFor(Browser $browser, $search)
    {
        $browser->type('@search', $search)->pause(1000);
    }

    /**
     * Clear the search field.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @return void
     */
    public function clearSearch(Browser $browser)
    {
        $browser->clear('@search')->type('@search', ' ')->pause(1000);
    }

    /**
     * Click the sortable icon for the given attribute.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @param  string  $attribute
     * @return void
     */
    public function sortBy(Browser $browser, $attribute)
    {
        $browser->click('@sort-'.$attribute)->waitForTable();
    }

    /**
     * Paginate to the next page of resources.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @return void
     */
    public function nextPage(Browser $browser)
    {
        return $browser->click('@next')->waitForTable();
    }

    /**
     * Paginate to the previous page of resources.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @return void
     */
    public function previousPage(Browser $browser)
    {
        return $browser->click('@previous')->waitForTable();
    }

    /**
     * Select all the matching resources.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @return void
     */
    public function selectAllMatching(Browser $browser)
    {
        $browser->click('[dusk="select-all-dropdown"]')
                        ->elsewhere('', function ($browser) {
                            $browser->whenAvailable('[dusk="select-all-matching-button"]', function ($browser) {
                                $browser->click('input[type="checkbox"]')->pause(250);
                            });
                        })
                        ->click('')
                        ->pause(250);
    }

    /**
     * Open the filter selector.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @return void
     *
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function openFilterSelector(Browser $browser)
    {
        $browser->waitFor('@filter-selector')
                    ->click('@filter-selector')
                    ->pause(100);
    }

    /**
     * Set the per page value for the index.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @return void
     */
    public function setPerPage(Browser $browser, $value)
    {
        $browser->openFilterSelector()
                    ->elsewhere('', function ($browser) use ($value) {
                        $browser->whenAvailable('@per-page-select', function ($browser) use ($value) {
                            $browser->select('', $value);
                        });
                    })
                    ->pause(250);
    }

    /**
     * Set the given filter and filter value for the index.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @param  string  $name
     * @param  string  $value
     * @return void
     */
    public function applyFilter(Browser $browser, $name, $value)
    {
        $browser->openFilterSelector()
                    ->pause(500)
                    ->elsewhere('', function ($browser) use ($name, $value) {
                        $browser->select('[dusk="'.$name.'-filter-select"]', $value);
                    })->click('')->pause(250);
    }

    /**
     * Indicate that trashed records should not be displayed.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @return void
     */
    public function withoutTrashed(Browser $browser)
    {
        $browser->openFilterSelector()
                ->elsewhere('', function ($browser) {
                    $browser->whenAvailable('[dusk="filter-soft-deletes"]', function ($browser) {
                        $browser->select('[dusk="trashed-select"]', '');
                    })->click('')->pause(350);
                });
    }

    /**
     * Indicate that only trashed records should be displayed.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @return void
     */
    public function onlyTrashed(Browser $browser)
    {
        $browser->openFilterSelector()
                ->elsewhere('', function ($browser) {
                    $browser->whenAvailable('[dusk="filter-soft-deletes"]', function ($browser) {
                        $browser->select('[dusk="trashed-select"]', 'only');
                    })->click('')->pause(350);
                });
    }

    /**
     * Indicate that trashed records should be displayed.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @return void
     */
    public function withTrashed(Browser $browser)
    {
        $browser->openFilterSelector()
                ->elsewhere('', function ($browser) {
                    $browser->whenAvailable('[dusk="filter-soft-deletes"]', function ($browser) {
                        $browser->select('[dusk="trashed-select"]', 'with');
                    })->click('')->pause(350);
                });
    }

    /**
     * Open the action selector.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @return void
     *
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function openActionSelector(Browser $browser)
    {
        $browser->waitFor('@action-select')
                    ->click('@action-select')
                    ->pause(100);
    }

    /**
     * Run the action with the given URI key.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @return void
     *
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function runAction(Browser $browser, $uriKey, $fieldCallback = null)
    {
        $browser->waitFor('@action-select')
                    ->select('@action-select', $uriKey)
                    ->pause(100)
                    ->click('@run-action-button');

        $browser->elsewhere('', function ($browser) use ($fieldCallback) {
            $browser->whenAvailable('.modal', function ($browser) use ($fieldCallback) {
                if ($fieldCallback) {
                    $fieldCallback($browser);
                }

                $browser->click('[dusk="confirm-action-button"]')->pause(250);
            });
        });
    }

    /**
     * Run the action with the given URI key.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @param  int|string  $id
     * @param  string  $uriKey
     * @param  callable  $fieldCallback
     * @return void
     */
    public function runInlineAction(Browser $browser, $id, $uriKey, $fieldCallback = null)
    {
        $browser->within('[dusk="'.$id.'-row"]', function ($browser) use ($uriKey) {
            $browser->click('[dusk="run-inline-action-button"][data-testid="'.$uriKey.'"]');
        });

        $browser->elsewhere('', function ($browser) use ($fieldCallback) {
            $browser->whenAvailable('.modal', function ($browser) use ($fieldCallback) {
                if ($fieldCallback) {
                    $fieldCallback($browser);
                }

                $browser->click('[dusk="confirm-action-button"]')->pause(250);
            });
        });
    }

    /**
     * Check the user at the given resource table row index.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @param  int|string  $id
     * @return void
     */
    public function clickCheckboxForId(Browser $browser, $id)
    {
        $browser->click('[dusk="'.$id.'-row"] input.checkbox')
                        ->pause(175);
    }

    /**
     * Delete the user at the given resource table row index.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @param  int|string  $id
     * @return void
     */
    public function deleteResourceById(Browser $browser, $id)
    {
        $browser->click('@'.$id.'-delete-button')
                        ->elsewhere('', function ($browser) {
                            $browser->whenAvailable('.modal', function ($browser) {
                                $browser->click('#confirm-delete-button');
                            });
                        })->pause(500);
    }

    /**
     * Restore the user at the given resource table row index.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @param  int|string  $id
     * @return void
     */
    public function restoreResourceById(Browser $browser, $id)
    {
        $browser->click('@'.$id.'-restore-button')
                        ->elsewhere('', function ($browser) {
                            $browser->whenAvailable('.modal', function ($browser) {
                                $browser->click('#confirm-restore-button');
                            });
                        })->pause(500);
    }

    /**
     * Delete the resources selected via checkboxes.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @return void
     */
    public function deleteSelected(Browser $browser)
    {
        $browser->click('@delete-menu')
                    ->pause(300)
                    ->elsewhere('', function ($browser) {
                        $browser->click('[dusk="delete-selected-button"]')
                            ->whenAvailable('.modal', function ($browser) {
                                $browser->click('#confirm-delete-button');
                            });
                    })->pause(1000);
    }

    /**
     * Restore the resources selected via checkboxes.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @return void
     */
    public function restoreSelected(Browser $browser)
    {
        $browser->click('@delete-menu')
                    ->pause(300)
                    ->elsewhere('', function ($browser) {
                        $browser->click('[dusk="restore-selected-button"]')
                            ->whenAvailable('.modal', function ($browser) {
                                $browser->click('#confirm-restore-button');
                            });
                    })->pause(1000);
    }

    /**
     * Restore the resources selected via checkboxes.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @return void
     */
    public function forceDeleteSelected(Browser $browser)
    {
        $browser->click('@delete-menu')
                    ->pause(300)
                    ->elsewhere('', function ($browser) {
                        $browser->click('[dusk="force-delete-selected-button"]')
                            ->whenAvailable('.modal', function ($browser) {
                                $browser->click('#confirm-delete-button');
                            });
                    })->pause(1000);
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
        $browser->pause(500);

        tap($this->selector(), function ($selector) use ($browser) {
            $browser->waitFor($selector, 25)
                    ->assertVisible($selector);
        });
    }

    /**
     * Assert that the given resource is visible.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @param  int|string  $id
     * @return void
     */
    public function assertSeeResource(Browser $browser, $id)
    {
        $browser->assertVisible('@'.$id.'-row');
    }

    /**
     * Assert that the given resource is not visible.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @param  int|string  $id
     * @return void
     */
    public function assertDontSeeResource(Browser $browser, $id)
    {
        $browser->assertMissing('@'.$id.'-row');
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
        $browser->click('@select-all-dropdown')
                        ->elsewhere('', function (Browser $browser) use ($count) {
                            $browser->whenAvailable('@select-all-matching-button', function (Browser $browser) use ($count) {
                                $browser->assertSee('('.$count.')');
                            });
                        });
    }

    /**
     * Get the element shortcuts for the component.
     *
     * @return array
     */
    public function elements()
    {
        return [];
    }
}
