<?php

namespace Laravel\Nova\Tests\Feature;

use Cake\Chronos\Chronos;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Tests\Fixtures\AverageWordCount;
use Laravel\Nova\Tests\Fixtures\Post;
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

    public function test_metric_can_be_calculated()
    {
        factory(User::class, 2)->create();

        $this->assertEquals(2, (new TotalUsers)->calculate(NovaRequest::create('/'))->value);
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
}
