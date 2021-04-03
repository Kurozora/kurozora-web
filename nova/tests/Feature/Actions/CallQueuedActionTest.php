<?php

namespace Laravel\Nova\Tests\Feature\Actions;

use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\CallQueuedAction;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Tests\Fixtures\User;
use Laravel\Nova\Tests\IntegrationTest;

class CallQueuedActionTest extends IntegrationTest
{
    public function test_it_can_serialize_and_unserialize_models()
    {
        factory(User::class, 10)->create();

        $action = new Action();
        $method = 'handle';
        $fields = new ActionFields(collect(), collect());

        $queue = new CallQueuedAction($action, $method, $fields, User::all(), 'secret');

        $serialized = serialize($queue);

        $this->assertTrue(is_string($serialized));

        $unserialized = unserialize($serialized);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $unserialized->models);
        $this->assertInstanceOf(User::class, $unserialized->models[0]);
    }

    public function test_it_can_serialize_and_unserialize_collections()
    {
        $action = new Action();
        $method = 'handle';
        $fields = new ActionFields(collect(), collect());

        $queue = new CallQueuedAction($action, $method, $fields, collect([1, 2, 3]), 'secret');

        $serialized = serialize($queue);

        $this->assertTrue(is_string($serialized));

        $unserialized = unserialize($serialized);

        $this->assertInstanceOf('Illuminate\Support\Collection', $unserialized->models);
        $this->assertSame(1, $unserialized->models[0]);
        $this->assertSame(2, $unserialized->models[1]);
        $this->assertSame(3, $unserialized->models[2]);
    }
}
