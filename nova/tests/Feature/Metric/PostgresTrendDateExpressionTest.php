<?php

namespace Laravel\Nova\Tests\Feature\Metrics;

use Laravel\Nova\Metrics\PostgresTrendDateExpression;
use Laravel\Nova\Tests\Fixtures\User;
use Laravel\Nova\Tests\PostgresIntegrationTest;

class PostgresTrendDateExpressionTest extends PostgresIntegrationTest
{
    /**
     * @test
     * @dataProvider offsetDataProvider
     */
    public function it_can_handle_setting_offset($unit, $appTimezone, $userTimezone, $offset, $value)
    {
        config(['app.timezone' => $appTimezone]);

        $query = User::query();

        $trend = new PostgresTrendDateExpression($query, 'created_at', $unit, $userTimezone);

        $this->assertSame($offset, $trend->offset());
        $this->assertSame($value, $trend->getValue());
    }

    public function offsetDataProvider()
    {
        // [$unit, $appTimezone, $userTimezone, $offset, $value]

        yield ['month', 'UTC', 'Japan', 9, "to_char(\"created_at\" + interval '9 hour', 'YYYY-MM')"];
        yield ['month', 'UTC', 'Asia/Kuala_Lumpur', 8, "to_char(\"created_at\" + interval '8 hour', 'YYYY-MM')"];
        yield ['month', 'UTC', 'UTC', 0, "to_char(\"created_at\" , 'YYYY-MM')"];
        yield ['month', 'UTC', 'America/New_York', -5, "to_char(\"created_at\" - interval '5 HOUR', 'YYYY-MM')"];
        yield ['month', 'UTC', 'America/Chicago', -6, "to_char(\"created_at\" - interval '6 HOUR', 'YYYY-MM')"];

        yield ['month', 'Asia/Kuala_Lumpur', 'Japan', 1, "to_char(\"created_at\" + interval '1 hour', 'YYYY-MM')"];
        yield ['month', 'Asia/Kuala_Lumpur', 'Asia/Kuala_Lumpur', 0, "to_char(\"created_at\" , 'YYYY-MM')"];
        yield ['month', 'Japan', 'Asia/Kuala_Lumpur', -1, "to_char(\"created_at\" - interval '1 HOUR', 'YYYY-MM')"];
        yield ['month', 'Asia/Kuala_Lumpur', 'UTC', -8, "to_char(\"created_at\" - interval '8 HOUR', 'YYYY-MM')"];

        yield ['month', 'America/Chicago', 'America/New_York', 1, "to_char(\"created_at\" + interval '1 hour', 'YYYY-MM')"];
        yield ['month', 'America/New_York', 'America/Chicago', -1, "to_char(\"created_at\" - interval '1 HOUR', 'YYYY-MM')"];
    }
}
