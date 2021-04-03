<?php

namespace Laravel\Nova\Tests\Feature\Metrics;

use Laravel\Nova\Metrics\MySqlTrendDateExpression;
use Laravel\Nova\Tests\Fixtures\User;
use Laravel\Nova\Tests\MySqlIntegrationTest;

class MySqlTrendDateExpressionTest extends MySqlIntegrationTest
{
    /**
     * @test
     * @dataProvider offsetDataProvider
     */
    public function it_can_handle_setting_offset($unit, $appTimezone, $userTimezone, $offset, $value)
    {
        config(['app.timezone' => $appTimezone]);

        $query = User::query();

        $trend = new MySqlTrendDateExpression($query, 'created_at', $unit, $userTimezone);

        $this->assertSame($offset, $trend->offset());
        $this->assertSame($value, $trend->getValue());
    }

    public function offsetDataProvider()
    {
        // [$unit, $appTimezone, $userTimezone, $offset, $value]

        yield ['month', 'UTC', 'Japan', 9, "date_format(`created_at` + INTERVAL 9 HOUR, '%Y-%m')"];
        yield ['month', 'UTC', 'Asia/Kuala_Lumpur', 8, "date_format(`created_at` + INTERVAL 8 HOUR, '%Y-%m')"];
        yield ['month', 'UTC', 'UTC', 0, "date_format(`created_at` , '%Y-%m')"];
        yield ['month', 'UTC', 'America/New_York', -5, "date_format(`created_at` - INTERVAL 5 HOUR, '%Y-%m')"];
        yield ['month', 'UTC', 'America/Chicago', -6, "date_format(`created_at` - INTERVAL 6 HOUR, '%Y-%m')"];

        yield ['month', 'Asia/Kuala_Lumpur', 'Japan', 1, "date_format(`created_at` + INTERVAL 1 HOUR, '%Y-%m')"];
        yield ['month', 'Asia/Kuala_Lumpur', 'Asia/Kuala_Lumpur', 0, "date_format(`created_at` , '%Y-%m')"];
        yield ['month', 'Japan', 'Asia/Kuala_Lumpur', -1, "date_format(`created_at` - INTERVAL 1 HOUR, '%Y-%m')"];
        yield ['month', 'Asia/Kuala_Lumpur', 'UTC', -8, "date_format(`created_at` - INTERVAL 8 HOUR, '%Y-%m')"];

        yield ['month', 'America/Chicago', 'America/New_York', 1, "date_format(`created_at` + INTERVAL 1 HOUR, '%Y-%m')"];
        yield ['month', 'America/New_York', 'America/Chicago', -1, "date_format(`created_at` - INTERVAL 1 HOUR, '%Y-%m')"];
    }
}
