<?php

namespace Laravel\Nova\Testing\Browser\Components;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Component as BaseComponent;

class IndexComponent extends BaseComponent
{
    public $resourceName;

    /**
     * Create a new component instance.
     *
     * @param  string  $resourceName
     * @return void
     */
    public function __construct($resourceName)
    {
        $this->resourceName = $resourceName;
    }

    /**
     * Get the root selector for the component.
     *
     * @return string
     */
    public function selector()
    {
        return '@'.$this->resourceName.'-index-component';
    }

    /**
     * Wait for table to be ready.
     */
    public function waitForTable(Browser $browser, $seconds = null)
    {
        $browser->waitFor('table[data-testid="resource-table"]', $seconds);
    }

    /**
     * Search for the given string.
     */
    public function searchFor(Browser $browser, $search)
    {
        $browser->type('@search', $search)->pause(1000);
    }

    /**
     * Clear the search field.
     */
    public function clearSearch(Browser $browser)
    {
        $browser->clear('@search')->type('@search', ' ')->pause(1000);
    }

    /**
     * Click the sortable icon for the given attribute.
     */
    public function sortBy(Browser $browser, $attribute)
    {
        $browser->click('@sort-'.$attribute)->pause(500);
    }

    /**
     * Select all the matching resources.
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
     * Set the per page value for the index.
     */
    public function setPerPage(Browser $browser, $value)
    {
        $browser->click('@filter-selector')
                    ->elsewhere('', function ($browser) use ($value) {
                        $browser->whenAvailable('@per-page-select', function ($browser) use ($value) {
                            $browser->select('', $value);
                        });
                    })
                    ->pause(250);
    }

    /**
     * Paginate to the next page of resources.
     */
    public function nextPage(Browser $browser)
    {
        return $browser->click('@next')->pause(500);
    }

    /**
     * Paginate to the previous page of resources.
     */
    public function previousPage(Browser $browser)
    {
        return $browser->click('@previous')->pause(500);
    }

    /**
     * Set the given filter and filter value for the index.
     */
    public function applyFilter(Browser $browser, $name, $value)
    {
        $browser->click('@filter-selector')
                    ->pause(500)
                    ->elsewhere('', function ($browser) use ($name, $value) {
                        $browser->select('[dusk="'.$name.'-filter-select"]', $value);
                    })->click('')->pause(250);
    }

    /**
     * Indicate that trashed records should not be displayed.
     */
    public function withoutTrashed(Browser $browser)
    {
        $browser->click('@filter-selector')
                ->elsewhere('', function ($browser) {
                    $browser->whenAvailable('[dusk="filter-soft-deletes"]', function ($browser) {
                        $browser->select('[dusk="trashed-select"]', '');
                    })->click('')->pause(350);
                });
    }

    /**
     * Indicate that only trashed records should be displayed.
     */
    public function onlyTrashed(Browser $browser)
    {
        $browser->click('@filter-selector')
                ->elsewhere('', function ($browser) {
                    $browser->whenAvailable('[dusk="filter-soft-deletes"]', function ($browser) {
                        $browser->select('[dusk="trashed-select"]', 'only');
                    })->click('')->pause(350);
                });
    }

    /**
     * Indicate that trashed records should be displayed.
     */
    public function withTrashed(Browser $browser)
    {
        $browser->click('@filter-selector')
                ->elsewhere('', function ($browser) {
                    $browser->whenAvailable('[dusk="filter-soft-deletes"]', function ($browser) {
                        $browser->select('[dusk="trashed-select"]', 'with');
                    })->click('')->pause(350);
                });
    }

    /**
     * Open the action selector.
     */
    public function openActionSelector(Browser $browser)
    {
        $browser->click('@action-select')
                    ->pause(100);
    }

    /**
     * Run the action with the given URI key.
     */
    public function runAction(Browser $browser, $uriKey, $fieldCallback = null)
    {
        $browser->select('@action-select', $uriKey)
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
     */
    public function clickCheckboxForId(Browser $browser, $id)
    {
        $browser->click('[dusk="'.$id.'-row"] input.checkbox')
                        ->pause(175);
    }

    /**
     * Delete the user at the given resource table row index.
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
     * @param  Browser  $browser
     * @return void
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
     */
    public function assertSeeResource(Browser $browser, $id)
    {
        $browser->assertVisible('@'.$id.'-row');
    }

    /**
     * Assert that the given resource is not visible.
     */
    public function assertDontSeeResource(Browser $browser, $id)
    {
        $browser->assertMissing('@'.$id.'-row');
    }

    /**
     * Assert on the matching total matching count text.
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
