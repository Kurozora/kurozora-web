<?php

namespace Laravel\Nova\Tests\Feature;

use Cake\Chronos\Chronos;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\Trend;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Nova;
use Laravel\Nova\Tests\Fixtures\AverageWordCount;
use Laravel\Nova\Tests\Fixtures\Post;
use Laravel\Nova\Tests\Fixtures\PostAverageTrend;
use Laravel\Nova\Tests\Fixtures\PostCountTrend;
use Laravel\Nova\Tests\Fixtures\PostWithCustomCreatedAt;
use Laravel\Nova\Tests\Fixtures\TotalUsers;
use Laravel\Nova\Tests\Fixtures\User;
use Laravel\Nova\Tests\IntegrationTest;

class MetricTest extends IntegrationTest
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        DB::disableQueryLog();
        DB::flushQueryLog();

        parent::tearDown();
    }

    public function test_metric_can_be_calculated()
    {
        factory(User::class, 2)->create();

        $this->assertEquals(2, (new TotalUsers)->calculate(NovaRequest::create('/'))->value);
    }

    public function test_metric_calculation_using_user_timezone()
    {
        $metric = new class extends Value {
            public function calculate(NovaRequest $request)
            {
                return $this->count($request, User::class);
            }
        };

        factory(User::class)->create(['created_at' => Carbon::parse('Oct 14 2019 4 pm')]); // 11am Chicago, 12pm New York
        factory(User::class)->create(['created_at' => Carbon::parse('Oct 14 2019 5 pm')]); // 12pm Chicago, 1pm New York

        Carbon::setTestNow(Carbon::parse('Oct 14 2019 10 am', 'America/Chicago'));

        $request = NovaRequest::create('/', 'GET', ['timezone' => 'America/Chicago']);
        $this->assertEquals(0, $metric->calculate($request)->value);

        Carbon::setTestNow(Carbon::parse('Oct 14 2019 11 am', 'America/Chicago'));

        $request = NovaRequest::create('/', 'GET', ['timezone' => 'America/Chicago']);
        $this->assertEquals(1, $metric->calculate($request)->value);

        Carbon::setTestNow(Carbon::parse('Oct 14 2019 12 pm', 'America/Chicago'));

        $request = NovaRequest::create('/', 'GET', ['timezone' => 'America/Chicago']);
        $this->assertEquals(2, $metric->calculate($request)->value);

        Carbon::setTestNow(Carbon::parse('Oct 14 2019 11 am', 'America/New_York'));

        $request = NovaRequest::create('/', 'GET', ['timezone' => 'America/New_York']);
        $this->assertEquals(0, $metric->calculate($request)->value);

        Carbon::setTestNow(Carbon::parse('Oct 14 2019 12 pm', 'America/New_York'));

        $request = NovaRequest::create('/', 'GET', ['timezone' => 'America/New_York']);
        $this->assertEquals(1, $metric->calculate($request)->value);

        Carbon::setTestNow(null);
    }

    public function test_metric_calculation_uses_custom_timezone()
    {
        Nova::userTimezone(function () {
            return 'UTC';
        });

        $metric = new class extends Value {
            public function calculate(NovaRequest $request)
            {
                return $this->count($request, User::class);
            }
        };

        $now = Carbon::parse('Oct 14 2019 5 pm'); // UTC (future time)
        $nowCentral = $now->copy()->tz('America/Chicago'); // Now for the user

        Carbon::setTestNow(Carbon::parse($nowCentral));

        factory(User::class)->create(['created_at' => $now]);
        factory(User::class)->create(['created_at' => $nowCentral]);

        // Note even if we send the user's timezone, it should still use UTC
        $request = NovaRequest::create('/', 'GET', ['timezone' => 'America/Chicago']);

        $this->assertEquals(2, $metric->calculate($request)->value);

        Carbon::setTestNow(null);

        Nova::userTimezone(null);
    }

    public function test_trend_with_custom_created_at()
    {
        factory(Post::class, 2)->create();

        $post = Post::find(1);
        $post->published_at = Chronos::now();
        $post->save();

        $post = Post::find(2);
        $post->published_at = Chronos::now()->subDay(1);
        $post->save();

        $this->assertEquals([1, 1], array_values((new PostCountTrend())->countByDays(NovaRequest::create('/?range=2'), new PostWithCustomCreatedAt)->trend));
    }

    public function test_trend_calculation_using_user_timezone()
    {
        $metric = new class extends Trend {
        };

        Chronos::setTestNow(Chronos::parse('Dec 14 2019', 'UTC'));

        $now = Chronos::parse('Nov 1 2019 6:30 AM', 'UTC');

        $nowCentral = Chronos::parse('Nov 2 2019 12 AM', 'UTC');

        factory(User::class, 2)->create(['created_at' => $now]);
        factory(User::class, 7)->create(['created_at' => $nowCentral]);

        $request = NovaRequest::create('/?range=2', 'GET', ['timezone' => 'America/Chicago']);
        $this->assertEquals([
            'October 2019' => 0,
            'November 2019' => 9,
        ], $metric->countByMonths($request, User::class)->trend);

        $request = NovaRequest::create('/?range=2', 'GET', ['timezone' => 'America/Los_Angeles']);
        $this->assertEquals([
            'October 2019' => 2,
            'November 2019' => 7,
        ], $metric->countByMonths($request, User::class)->trend);

        Chronos::setTestNow(Chronos::parse('Nov 2 2019 8 AM', 'Japan'));

        $request = NovaRequest::create('/?range=2', 'GET', ['timezone' => 'Japan']);
        $this->assertEquals([
            'October 31, 2019' => 0,
            'November 1, 2019' => 2,
        ], $metric->countByDays($request, User::class)->trend);

        Chronos::setTestNow(Chronos::parse('Nov 2 2019 9 AM', 'Japan'));

        $request = NovaRequest::create('/?range=2', 'GET', ['timezone' => 'Japan']);
        $this->assertEquals([
            'November 1, 2019' => 2,
            'November 2, 2019' => 7,
        ], $metric->countByDays($request, User::class)->trend);

        Chronos::setTestNow(null);
    }

    public function test_trend_calculation_using_custom_timezone()
    {
        Nova::userTimezone(function () {
            return 'UTC';
        });

        $metric = new class extends Trend {
        };

        Chronos::setTestNow(Chronos::parse('Dec 14 2019', 'UTC'));

        $now = Chronos::parse('Nov 1 2019 6:30 AM', 'UTC');

        $nowCentral = Chronos::parse('Nov 2 2019 12 AM', 'UTC');

        factory(User::class, 2)->create(['created_at' => $now]);
        factory(User::class, 7)->create(['created_at' => $nowCentral]);

        $request = NovaRequest::create('/?range=2', 'GET', ['timezone' => 'America/Chicago']);
        $this->assertEquals([9, 0], array_values($metric->countByMonths($request, User::class)->trend));

        $request = NovaRequest::create('/?range=2', 'GET', ['timezone' => 'America/Los_Angeles']);
        $this->assertEquals([9, 0], array_values($metric->countByMonths($request, User::class)->trend));

        Chronos::setTestNow(null);

        Nova::userTimezone(null);
    }

    public function test_metrics_can_be_set_to_refresh_automatically()
    {
        $metric = new PostCountTrend;

        $this->assertfalse($metric->jsonSerialize()['refreshWhenActionRuns']);

        $metric->refreshWhenActionRuns();

        $this->assertTrue($metric->jsonSerialize()['refreshWhenActionRuns']);

        $metric->refreshWhenActionRuns(false);

        $this->assertFalse($metric->jsonSerialize()['refreshWhenActionRuns']);
    }

    public function test_value_metrics_default_precision()
    {
        $averageWordCount = factory(Post::class, 2)->create()->average('word_count');
        $this->assertEquals($averageWordCount, (new AverageWordCount)->calculate(NovaRequest::create('/'))->value);
    }

    public function test_value_metrics_custom_precision()
    {
        $averageWordCount = factory(Post::class, 2)->create(['word_count' => 5.37894])->average('word_count');
        $this->assertEquals($averageWordCount, 5.37894);
        $this->assertEquals(5.38, (new AverageWordCount)->precision(2)->calculate(NovaRequest::create('/'))->value);
    }

    public function test_trend_metrics_default_precision()
    {
        Carbon::setTestNow($now = now());

        factory(Post::class, 2)->create(['word_count' => 5.37894, 'published_at' => $now])->average('word_count');

        DB::enableQueryLog();
        DB::flushQueryLog();

        $this->assertEquals(5, Arr::first((new PostAverageTrend)->calculate(NovaRequest::create('/', 'GET', ['range'=>1]))->trend));

        $this->assertSame([
            Carbon::today()->startOfMonth()->toDatetimeString(), $now->toDatetimeString(),
        ], array_map(function ($date) {
            return $date->toDatetimeString();
        }, DB::getQueryLog()[0]['bindings']));
    }

    public function test_trend_metrics_custom_precision()
    {
        Carbon::setTestNow($now = now());

        factory(Post::class, 2)->create(['word_count' => 5.37894, 'published_at' => $now])->average('word_count');

        DB::enableQueryLog();
        DB::flushQueryLog();

        $this->assertEquals(5.38, Arr::first((new PostAverageTrend)->precision(2)->calculate(NovaRequest::create('/', 'GET', ['range'=>1]))->trend));

        $this->assertSame([
            Carbon::today()->startOfMonth()->toDatetimeString(), $now->toDatetimeString(),
        ], array_map(function ($date) {
            return $date->toDatetimeString();
        }, DB::getQueryLog()[0]['bindings']));
    }

    public function test_trend_metrics_exceeds_range()
    {
        Carbon::setTestNow($now = now());

        factory(Post::class, 2)->create(['word_count' => 5.37894, 'published_at' => $now])->average('word_count');

        DB::enableQueryLog();
        DB::flushQueryLog();

        $this->assertEquals(5, Arr::last((new PostAverageTrend)->calculate(NovaRequest::create('/', 'GET', ['range'=>24]))->trend));

        $this->assertSame([
            Carbon::today()->startOfMonth()->subMonths(11)->toDatetimeString(), $now->toDatetimeString(),
        ], array_map(function ($date) {
            return $date->toDatetimeString();
        }, DB::getQueryLog()[0]['bindings']));
    }

    public function test_value_metrics_can_provide_a_default_range()
    {
        $metric = new TotalUsers;

        $metric->ranges = [
            1 => 'January',
            2 => 'February',
        ];

        $metric->defaultRange(1);

        $this->assertEquals(1, $metric->jsonSerialize()['selectedRangeKey']);
    }

    public function test_value_metrics_default_range_defaults_to_null()
    {
        $metric = new TotalUsers;

        $this->assertNull($metric->jsonSerialize()['selectedRangeKey']);
    }

    public function test_partition_metrics_can_provide_data_with_raw_column_expression()
    {
        DB::enableQueryLog();
        DB::flushQueryLog();

        $metric = new class extends Partition {
            public function calculate(Request $request)
            {
                return $this->max($request, User::class, DB::raw('json_extract(meta, "$.value")'), 'id');
            }
        };

        $request = NovaRequest::create('/', 'GET', []);

        $metric->calculate($request);

        $this->assertSame(
            'select "id", max(json_extract(meta, "$.value")) as aggregate from "users" where "users"."deleted_at" is null group by "id"',
            DB::getQueryLog()[0]['query']
        );
    }

    public function test_trend_metrics_can_provide_data_with_raw_column_expression()
    {
        DB::enableQueryLog();
        DB::flushQueryLog();

        $metric = new class extends Trend {
            public function calculate(Request $request)
            {
                return $this->max($request, User::class, 'day', DB::raw('json_extract(meta, "$.value")'));
            }
        };

        $request = NovaRequest::create('/', 'GET', []);

        $metric->calculate($request);

        $this->assertSame(
            'select strftime(\'%Y-%m-%d\', datetime("users"."created_at", \'+0 hour\')) as date_result, max(json_extract(meta, "$.value")) as aggregate from "users" where "users"."created_at" between ? and ? and "users"."deleted_at" is null group by strftime(\'%Y-%m-%d\', datetime("users"."created_at", \'+0 hour\')) order by "date_result" asc',
            DB::getQueryLog()[0]['query']
        );
    }

    public function test_value_metrics_can_provide_data_with_raw_column_expression()
    {
        DB::enableQueryLog();
        DB::flushQueryLog();

        $metric = new class extends Value {
            public function calculate(Request $request)
            {
                return $this->max($request, User::class, DB::raw('json_extract(meta, "$.value")'));
            }
        };

        $request = NovaRequest::create('/', 'GET', []);

        $metric->calculate($request);

        $this->assertSame(
            'select max(json_extract(meta, "$.value")) as aggregate from "users" where "users"."created_at" between ? and ? and "users"."deleted_at" is null',
            DB::getQueryLog()[0]['query']
        );
    }

    /**
     * @dataProvider userTimezoneForTodayRangesDataProvider
     */
    public function test_value_metric_display_today_range_values_for_user_timezone(
        $userTimezone,
        $previousStarting,
        $previousEnding,
        $currentStarting,
        $previousValue,
        $currentValue
    ) {
        Carbon::setTestNow(Carbon::parse('2021-01-31 20:00:00'));

        factory(User::class)->create(['created_at' => '2021-01-30 19:00:00']);
        factory(User::class)->create(['created_at' => '2021-01-30 20:00:00']);
        factory(User::class)->create(['created_at' => '2021-01-30 21:00:00']);
        factory(User::class)->create(['created_at' => '2021-01-30 22:00:00']);
        factory(User::class)->create(['created_at' => '2021-01-30 23:00:00']);
        factory(User::class)->create(['created_at' => '2021-01-31 00:00:00']);
        factory(User::class)->create(['created_at' => '2021-01-31 01:00:00']);
        factory(User::class)->create(['created_at' => '2021-01-31 02:00:00']);
        factory(User::class)->create(['created_at' => '2021-01-31 03:00:00']);
        factory(User::class)->create(['created_at' => '2021-01-31 04:00:00']);
        factory(User::class)->create(['created_at' => '2021-01-31 05:00:00']);
        factory(User::class)->create(['created_at' => '2021-01-31 06:00:00']);
        factory(User::class)->create(['created_at' => '2021-01-31 07:00:00']);
        factory(User::class)->create(['created_at' => '2021-01-31 08:00:00']);
        factory(User::class)->create(['created_at' => '2021-01-31 09:00:00']);
        factory(User::class)->create(['created_at' => '2021-01-31 10:00:00']);
        factory(User::class)->create(['created_at' => '2021-01-31 11:00:00']);
        factory(User::class)->create(['created_at' => '2021-01-31 12:00:00']);
        factory(User::class)->create(['created_at' => '2021-01-31 13:00:00']);
        factory(User::class)->create(['created_at' => '2021-01-31 14:00:00']);
        factory(User::class)->create(['created_at' => '2021-01-31 15:00:00']);
        factory(User::class)->create(['created_at' => '2021-01-31 16:00:00']);
        factory(User::class)->create(['created_at' => '2021-01-31 17:00:00']);
        factory(User::class)->create(['created_at' => '2021-01-31 18:00:00']);
        factory(User::class)->create(['created_at' => '2021-01-31 19:00:00']);
        factory(User::class)->create(['created_at' => '2021-01-31 20:00:00']);

        DB::enableQueryLog();
        DB::flushQueryLog();

        $metric = new class extends Value {
            public function calculate(NovaRequest $request)
            {
                return $this->count($request, User::class);
            }
        };

        $request = NovaRequest::create('/', 'GET', ['range' => 'TODAY', 'timezone' => $userTimezone]);

        $valueResult = $metric->calculate($request);

        $this->assertSame(
            'select count("users"."id") as aggregate from "users" where "users"."created_at" between ? and ? and "users"."deleted_at" is null',
            DB::getQueryLog()[0]['query']
        );

        $this->assertSame($previousStarting, DB::getQueryLog()[0]['bindings'][0]->toDatetimeString());
        $this->assertSame($previousEnding, DB::getQueryLog()[0]['bindings'][1]->toDatetimeString());

        $this->assertSame(
            'select count("users"."id") as aggregate from "users" where "users"."created_at" between ? and ? and "users"."deleted_at" is null',
            DB::getQueryLog()[1]['query']
        );

        $this->assertSame($currentStarting, DB::getQueryLog()[1]['bindings'][0]->toDatetimeString());
        $this->assertSame('2021-01-31 20:00:00', DB::getQueryLog()[1]['bindings'][1]->toDatetimeString());

        $this->assertSame($currentValue, $valueResult->value);
        $this->assertSame($previousValue, $valueResult->previous);

        Carbon::setTestNow(null);
    }

    /**
     * @dataProvider userTimezoneForMTDRangesDataProvider
     */
    public function test_value_metric_display_mtd_range_values_for_user_timezone(
        $userTimezone,
        $previousStarting,
        $previousEnding,
        $currentStarting
    ) {
        Carbon::setTestNow(Carbon::parse('2021-01-31 20:00:00'));

        DB::enableQueryLog();
        DB::flushQueryLog();

        $metric = new class extends Value {
            public function calculate(NovaRequest $request)
            {
                return $this->count($request, User::class);
            }
        };

        $request = NovaRequest::create('/', 'GET', ['range' => 'MTD', 'timezone' => $userTimezone]);

        $valueResult = $metric->calculate($request);

        $this->assertSame($previousStarting, DB::getQueryLog()[0]['bindings'][0]->toDatetimeString());
        $this->assertSame($previousEnding, DB::getQueryLog()[0]['bindings'][1]->toDatetimeString());

        $this->assertSame($currentStarting, DB::getQueryLog()[1]['bindings'][0]->toDatetimeString());
        $this->assertSame('2021-01-31 20:00:00', DB::getQueryLog()[1]['bindings'][1]->toDatetimeString());

        Carbon::setTestNow(null);
    }

    /**
     * @dataProvider userTimezoneForQTDRangesDataProvider
     */
    public function test_value_metric_display_qtd_range_values_for_user_timezone(
        $userTimezone,
        $previousStarting,
        $previousEnding,
        $currentStarting
    ) {
        Carbon::setTestNow(Carbon::parse('2021-01-31 20:00:00'));

        DB::enableQueryLog();
        DB::flushQueryLog();

        $metric = new class extends Value {
            public function calculate(NovaRequest $request)
            {
                return $this->count($request, User::class);
            }
        };

        $request = NovaRequest::create('/', 'GET', ['range' => 'QTD', 'timezone' => $userTimezone]);

        $valueResult = $metric->calculate($request);

        $this->assertSame($previousStarting, DB::getQueryLog()[0]['bindings'][0]->toDatetimeString());
        $this->assertSame($previousEnding, DB::getQueryLog()[0]['bindings'][1]->toDatetimeString());

        $this->assertSame($currentStarting, DB::getQueryLog()[1]['bindings'][0]->toDatetimeString());
        $this->assertSame('2021-01-31 20:00:00', DB::getQueryLog()[1]['bindings'][1]->toDatetimeString());

        Carbon::setTestNow(null);
    }

    /**
     * @dataProvider userTimezoneForYTDRangesDataProvider
     */
    public function test_value_metric_display_ytd_range_values_for_user_timezone(
        $userTimezone,
        $previousStarting,
        $previousEnding,
        $currentStarting
    ) {
        Carbon::setTestNow(Carbon::parse('2021-01-31 20:00:00'));

        DB::enableQueryLog();
        DB::flushQueryLog();

        $metric = new class extends Value {
            public function calculate(NovaRequest $request)
            {
                return $this->count($request, User::class);
            }
        };

        $request = NovaRequest::create('/', 'GET', ['range' => 'YTD', 'timezone' => $userTimezone]);

        $valueResult = $metric->calculate($request);

        $this->assertSame($previousStarting, DB::getQueryLog()[0]['bindings'][0]->toDatetimeString());
        $this->assertSame($previousEnding, DB::getQueryLog()[0]['bindings'][1]->toDatetimeString());

        $this->assertSame($currentStarting, DB::getQueryLog()[1]['bindings'][0]->toDatetimeString());
        $this->assertSame('2021-01-31 20:00:00', DB::getQueryLog()[1]['bindings'][1]->toDatetimeString());

        Carbon::setTestNow(null);
    }

    /**
     * @dataProvider userTimezoneDataProvider
     */
    public function test_value_metric_display_5_days_range_values_for_user_timezone($userTimezone)
    {
        Carbon::setTestNow(Carbon::parse('2021-01-31 20:00:00'));

        DB::enableQueryLog();
        DB::flushQueryLog();

        $metric = new class extends Value {
            public function calculate(NovaRequest $request)
            {
                return $this->count($request, User::class);
            }
        };

        $request = NovaRequest::create('/', 'GET', ['range' => '5', 'timezone' => $userTimezone]);

        $valueResult = $metric->calculate($request);

        $this->assertSame('2021-01-21 20:00:00', DB::getQueryLog()[0]['bindings'][0]->toDatetimeString());
        $this->assertSame('2021-01-26 19:59:59', DB::getQueryLog()[0]['bindings'][1]->toDatetimeString());

        $this->assertSame('2021-01-26 20:00:00', DB::getQueryLog()[1]['bindings'][0]->toDatetimeString());
        $this->assertSame('2021-01-31 20:00:00', DB::getQueryLog()[1]['bindings'][1]->toDatetimeString());

        Carbon::setTestNow(null);
    }

    public function userTimezoneForTodayRangesDataProvider()
    {
        // [$timezone, $previousStarting, $previousEnding, $currentStarting, $previousValue, $currentValue]

        yield ['UTC', '2021-01-30 00:00:00', '2021-01-30 23:59:59', '2021-01-31 00:00:00', 5.0, 21.0];
        yield ['America/Los_Angeles', '2021-01-30 08:00:00', '2021-01-31 07:59:59', '2021-01-31 08:00:00', 13.0, 13.0];
        yield ['Africa/Johannesburg', '2021-01-29 22:00:00', '2021-01-30 21:59:59', '2021-01-30 22:00:00', 3.0, 23.0];
        yield ['Asia/Kuala_Lumpur', '2021-01-30 16:00:00', '2021-01-31 15:59:59', '2021-01-31 16:00:00', 21.0, 5.0];
    }

    public function userTimezoneForMTDRangesDataProvider()
    {
        // [$timezone, $previousStarting, $previousEnding, $currentStarting]

        yield ['UTC', '2020-12-01 00:00:00', '2020-12-31 23:59:59', '2021-01-01 00:00:00'];
        yield ['America/Los_Angeles', '2020-12-01 08:00:00', '2021-01-01 07:59:59', '2021-01-01 08:00:00'];
        yield ['Africa/Johannesburg', '2020-11-30 22:00:00', '2020-12-31 21:59:59', '2020-12-31 22:00:00'];
        yield ['Asia/Kuala_Lumpur', '2020-12-31 16:00:00', '2021-01-31 15:59:59', '2021-01-31 16:00:00'];
    }

    public function userTimezoneForQTDRangesDataProvider()
    {
        // [$timezone, $previousStarting, $previousEnding, $currentStarting]

        yield ['UTC', '2020-10-01 00:00:00', '2020-12-31 23:59:59', '2021-01-01 00:00:00'];
        yield ['America/Los_Angeles', '2020-09-30 07:00:00', '2020-12-31 07:59:59', '2020-12-31 08:00:00'];
        yield ['Africa/Johannesburg', '2020-09-30 22:00:00', '2020-12-31 21:59:59', '2020-12-31 22:00:00'];
        yield ['Asia/Kuala_Lumpur', '2020-09-30 16:00:00', '2020-12-31 15:59:59', '2020-12-31 16:00:00'];
    }

    public function userTimezoneForYTDRangesDataProvider()
    {
        // [$timezone, $previousStarting, $previousEnding, $currentStarting]

        yield ['UTC', '2020-01-01 00:00:00', '2020-12-31 23:59:59', '2021-01-01 00:00:00'];
        yield ['America/Los_Angeles', '2020-01-01 08:00:00', '2021-01-01 07:59:59', '2021-01-01 08:00:00'];
        yield ['Africa/Johannesburg', '2019-12-31 22:00:00', '2020-12-31 21:59:59', '2020-12-31 22:00:00'];
        yield ['Asia/Kuala_Lumpur', '2019-12-31 16:00:00', '2020-12-31 15:59:59', '2020-12-31 16:00:00'];
    }

    public function userTimezoneDataProvider()
    {
        yield ['America/Los_Angeles'];
        yield ['Africa/Johannesburg'];
        yield ['Asia/Kuala_Lumpur'];
    }
}
