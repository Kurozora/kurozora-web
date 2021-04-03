<?php

namespace Laravel\Nova\Tests\Controller;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Actions\ActionEvent;
use Laravel\Nova\Nova;
use Laravel\Nova\Tests\Fixtures\Role;
use Laravel\Nova\Tests\Fixtures\User;
use Laravel\Nova\Tests\Fixtures\UserPolicy;
use Laravel\Nova\Tests\IntegrationTest;

class ResourceAttachTest extends IntegrationTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->authenticate();
    }

    public function test_can_attach_resources()
    {
        $user = factory(User::class)->create([
            'id' => 9999,
        ]);
        $role = factory(Role::class)->create();

        $response = $this->withExceptionHandling()
                        ->postJson('/nova-api/users/'.$user->id.'/attach/roles', [
                            'roles' => $role->id,
                            'admin' => 'Y',
                            'viaRelationship' => 'roles',
                        ]);

        $response->assertStatus(200);

        $this->assertCount(1, User::first()->roles);

        $this->assertCount(1, ActionEvent::all());
        $this->assertEquals('Attach', ActionEvent::first()->name);

        $this->assertDatabaseHas('action_events', [
            'name' => 'Attach',
            'actionable_type' => 'Laravel\Nova\Tests\Fixtures\User',
            'actionable_id' => (string) $user->getKey(),
            'target_type' => 'Laravel\Nova\Tests\Fixtures\Role',
            'target_id' => (string) $role->getKey(),
            'model_type' => 'Laravel\Nova\Tests\Fixtures\RoleAssignment',
            'model_id' => null,
            'fields' => '',
            'status' => 'finished',
            'exception' => '',
            'original' => null,
        ]);
    }

    public function cant_attach_resources_not_authorized_to_attach()
    {
        $user = factory(User::class)->create([
            'id' => 9999,
        ]);
        $role = factory(Role::class)->create();

        $_SERVER['nova.user.authorizable'] = true;
        $_SERVER['nova.user.attachRole'] = false;

        Gate::policy(User::class, UserPolicy::class);

        $response = $this->withExceptionHandling()
                ->postJson('/nova-api/users/'.$user->id.'/attach/roles', [
                    'roles' => $role->id,
                    'admin' => 'Y',
                    'viaRelationship' => 'roles',
                ]);

        unset($_SERVER['nova.user.authorizable']);
        unset($_SERVER['nova.user.attachRole']);

        $response->dump()->assertStatus(200);

        $this->assertCount(0, User::first()->roles);

        $this->assertCount(0, ActionEvent::all());
    }

    public function test_should_store_action_event_on_correct_connection_when_attaching()
    {
        $this->setupActionEventsOnSeparateConnection();

        $user = factory(User::class)->create([
            'id' => 9999,
        ]);
        $role = factory(Role::class)->create();

        $response = $this->withExceptionHandling()
                        ->postJson('/nova-api/users/'.$user->id.'/attach/roles', [
                            'roles' => $role->id,
                            'admin' => 'Y',
                            'viaRelationship' => 'roles',
                        ]);

        $response->assertStatus(200);

        $this->assertCount(1, User::first()->roles);

        $this->assertCount(0, DB::connection('sqlite')->table('action_events')->get());
        $this->assertCount(1, DB::connection('sqlite-custom')->table('action_events')->get());

        tap(Nova::actionEvent()->first(), function ($actionEvent) use ($role) {
            $this->assertEquals('Attach', $actionEvent->name);
            $this->assertEquals($role->id, $actionEvent->target_id);
        });
    }
}
