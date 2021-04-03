<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Dock;
use App\Models\Ship;
use App\Models\User;
use Database\Factories\DockFactory;
use Database\Factories\ShipFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\Index;
use Laravel\Nova\Tests\DuskTestCase;

class SoftDeletingIndexTest extends DuskTestCase
{
    /**
     * @test
     */
    public function can_soft_delete_a_resource_via_resource_table_row_delete_icon()
    {
        $dock = DockFactory::new()->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Index('docks'))
                    ->within(new IndexComponent('docks'), function ($browser) {
                        $browser->waitForTable(25)
                                ->deleteResourceById(1)
                                ->waitForText('No dock matched the given criteria.', 25)
                                ->assertDontSeeResource(1);
                    });

            $this->assertEquals(1, Dock::withTrashed()->count());
        });
    }

    /**
     * @test
     */
    public function can_soft_delete_resources_using_checkboxes()
    {
        DockFactory::new()->create();
        DockFactory::new()->create();
        DockFactory::new()->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Index('docks'))
                    ->within(new IndexComponent('docks'), function ($browser) {
                        $browser->waitForTable(25)
                            ->clickCheckboxForId(3)
                            ->clickCheckboxForId(2)
                            ->deleteSelected()
                            ->assertSeeResource(1)
                            ->assertDontSeeResource(2)
                            ->assertDontSeeResource(3);
                    });
        });
    }

    /**
     * @test
     */
    public function can_restore_resources_using_checkboxes()
    {
        DockFactory::new()->create();
        DockFactory::new()->create(['deleted_at' => now()]);
        DockFactory::new()->create(['deleted_at' => now()]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Index('docks'))
                    ->within(new IndexComponent('docks'), function ($browser) {
                        $browser->withTrashed();

                        $browser->waitForTable(25)
                            ->clickCheckboxForId(3)
                            ->clickCheckboxForId(2)
                            ->restoreSelected()
                            ->withoutTrashed()
                            ->waitForText('Docks', 25)
                            ->assertSeeResource(1)
                            ->assertSeeResource(2)
                            ->assertSeeResource(3);
                    });
        });
    }

    /**
     * @test
     */
    public function can_force_delete_resources_using_checkboxes()
    {
        DockFactory::new()->create();
        DockFactory::new()->create();
        DockFactory::new()->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Index('docks'))
                    ->within(new IndexComponent('docks'), function ($browser) {
                        $browser->withTrashed();

                        $browser->waitForTable(25)
                            ->clickCheckboxForId(3)
                            ->clickCheckboxForId(2)
                            ->forceDeleteSelected()
                            ->assertSeeResource(1)
                            ->assertDontSeeResource(2)
                            ->assertDontSeeResource(3);
                    });
        });
    }

    /**
     * @test
     */
    public function can_soft_delete_all_matching_resources()
    {
        $dock = DockFactory::new()->create();
        $dock->ships()->saveMany(ShipFactory::new()->times(3)->create());

        $separateShip = ShipFactory::new()->create();

        $this->browse(function (Browser $browser) use ($separateShip) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('docks', 1))
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->waitForTable(25)
                            ->selectAllMatching()
                            ->deleteSelected()
                            ->assertDontSeeResource(1)
                            ->assertDontSeeResource(2)
                            ->assertDontSeeResource(3)
                            ->withTrashed()
                            ->assertSeeResource(1)
                            ->assertSeeResource(2)
                            ->assertSeeResource(3);
                    });

            $this->assertNull($separateShip->fresh()->deleted_at);
        });
    }

    /**
     * @test
     */
    public function can_restore_all_matching_resources()
    {
        $dock = DockFactory::new()->create();
        $dock->ships()->saveMany(ShipFactory::new()->times(3)->create(['deleted_at' => now()]));

        $separateShip = ShipFactory::new()->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('docks', 1))
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->withTrashed();

                        $browser->waitForTable(25)
                            ->selectAllMatching()
                            ->restoreSelected()
                            ->assertSeeResource(1)
                            ->assertSeeResource(2)
                            ->assertSeeResource(3);
                    });

            $this->assertEquals(4, Ship::count());
            $this->assertEquals(0, Ship::onlyTrashed()->count());
        });
    }

    /**
     * @test
     */
    public function can_force_delete_all_matching_resources()
    {
        $dock = DockFactory::new()->create();
        $dock->ships()->saveMany(ShipFactory::new()->times(3)->create(['deleted_at' => now()]));

        $separateShip = ShipFactory::new()->create();

        $this->browse(function (Browser $browser) use ($separateShip) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('docks', 1))
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->withTrashed();

                        $browser->waitForTable(25)
                            ->selectAllMatching()
                            ->forceDeleteSelected()
                            ->assertDontSeeResource(1)
                            ->assertDontSeeResource(2)
                            ->assertDontSeeResource(3);
                    });

            $this->assertNotNull($separateShip->fresh());
            $this->assertEquals(1, Ship::count());
            $this->assertEquals(0, Ship::onlyTrashed()->count());
        });
    }

    /**
     * @test
     */
    public function soft_deleted_resource_is_still_viewable_with_proper_trash_state()
    {
        $dock = DockFactory::new()->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Index('docks'))
                    ->within(new IndexComponent('docks'), function ($browser) {
                        $browser->withTrashed()
                                ->waitForTable(25)
                                ->deleteResourceById(1)
                                ->waitForTable(25)
                                ->assertSeeResource(1);
                    });

            $this->assertEquals(1, Dock::withTrashed()->count());
        });
    }

    /**
     * @test
     */
    public function only_soft_deleted_resources_may_be_listed()
    {
        DockFactory::new()->times(2)->create();
        Dock::find(2)->delete();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Index('docks'))
                    ->within(new IndexComponent('docks'), function ($browser) {
                        $browser->waitForTable(25)
                                ->assertSeeResource(1)
                                ->assertDontSeeResource(2);

                        $browser->onlyTrashed()
                                ->assertDontSeeResource(1)
                                ->assertSeeResource(2);
                    });
        });
    }

    /**
     * @test
     */
    public function soft_deleted_resources_may_be_restored_via_row_icon()
    {
        DockFactory::new()->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Index('docks'))
                    ->within(new IndexComponent('docks'), function ($browser) {
                        $browser->withTrashed()
                                ->waitForTable(25)
                                ->deleteResourceById(1)
                                ->waitForTable(25)
                                ->restoreResourceById(1)
                                ->waitForTable(25)
                                ->assertSeeResource(1);
                    });

            $this->assertEquals(1, Dock::count());
        });
    }
}
