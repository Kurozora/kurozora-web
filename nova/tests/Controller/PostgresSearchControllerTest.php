<?php

namespace Laravel\Nova\Tests\Controller;

use Laravel\Nova\Resource;
use Laravel\Nova\Tests\PostgresIntegrationTest;

class PostgresSearchControllerTest extends PostgresIntegrationTest
{
    use SearchControllerTests;

    public function setUp(): void
    {
        $this->skipIfNotRunning();

        parent::setUp();

        $this->authenticate();
    }

    public function tearDown(): void
    {
        Resource::$maxPrimaryKeySize = PHP_INT_MAX;

        parent::tearDown();
    }

    public function test_can_skipped_searching_id_when_given_above_max_primary_key()
    {
        Resource::$maxPrimaryKeySize = 2147483647;

        $this->test_cant_retrieve_search_results_by_ids_given_invalid_numeric('50058270226890');
    }
}
