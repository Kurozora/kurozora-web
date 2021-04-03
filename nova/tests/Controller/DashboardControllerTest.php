<?php

namespace Laravel\Nova\Tests\Controller;

use Laravel\Nova\Tests\IntegrationTest;

class DashboardControllerTest extends IntegrationTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->authenticate();
    }

    public function test_it_can_browse_main_dashboard()
    {
        $response = $this->withExceptionHandling()
            ->getJson('/nova-api/dashboards/main')
            ->assertOk()
            ->assertJson([
                'label' => 'Dashboard',
                'cards' => [],
            ]);
    }

    public function test_it_cant_browse_invalid_dashboard()
    {
        $response = $this->withExceptionHandling()
            ->getJson('/nova-api/dashboards/foobar')
            ->assertStatus(404);
    }
}
