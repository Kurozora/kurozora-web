<?php

namespace Laravel\Nova\Tests\Feature;

use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Http\Requests\ResourceDetailRequest;
use Laravel\Nova\Http\Requests\ResourceIndexRequest;
use Laravel\Nova\Tests\Fixtures\CustomFieldNameUserResource;
use Laravel\Nova\Tests\Fixtures\Post;
use Laravel\Nova\Tests\Fixtures\PostResource;
use Laravel\Nova\Tests\Fixtures\User;
use Laravel\Nova\Tests\Fixtures\UserResource;
use Laravel\Nova\Tests\Fixtures\UserWithCustomFields;
use Laravel\Nova\Tests\IntegrationTest;

class ResourceFieldTest extends IntegrationTest
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_can_resolve_fields()
    {
        $user = factory(User::class)->create();
        $resource = new UserResource($user);
        $request = NovaRequest::create('/');

        $this->assertInstanceOf(Collection::class, $resource->availableFields($request));
    }

    public function test_can_resolve_fields_with_empty_model()
    {
        $user = new User;
        $resource = new UserResource($user);
        $request = NovaRequest::create('/');

        $this->assertInstanceOf(Collection::class, $resource->availableFields($request));
    }

    public function test_missing_fields_are_removed()
    {
        $user = factory(User::class)->create();
        $resource = new UserResource($user);
        $request = NovaRequest::create('/');

        $this->assertCount(1, $resource->availableFields($request)->where('attribute', 'id'));
        $this->assertCount(0, $resource->availableFields($request)->where('attribute', 'test'));
    }

    public function test_id_is_automatically_added_when_serializing()
    {
        $post = factory(Post::class)->create();

        $resource = new PostResource($post);
        $request = NovaRequest::create('/');

        $this->assertNull($resource->availableFields($request)->where('attribute', 'id')->first());
        $this->assertEquals($post->id, $resource->serializeForIndex($request)['id']->value);
    }

    public function test_index_only_fields_are_respected()
    {
        $user = factory(User::class)->create();
        $resource = new UserResource($user);
        $request = NovaRequest::create('/');

        $this->assertCount(1, $resource->indexFields($request)->where('attribute', 'index'));
        $this->assertCount(0, $resource->creationFields($request)->where('attribute', 'index'));
        $this->assertCount(0, $resource->updateFields($request)->where('attribute', 'index'));
        $this->assertCount(0, $resource->detailFields($request)->where('attribute', 'index'));
    }

    public function test_detail_only_fields_are_respected()
    {
        $user = factory(User::class)->create();
        $resource = new UserResource($user);
        $request = NovaRequest::create('/');

        $this->assertCount(0, $resource->indexFields($request)->where('attribute', 'detail'));
        $this->assertCount(0, $resource->creationFields($request)->where('attribute', 'detail'));
        $this->assertCount(0, $resource->updateFields($request)->where('attribute', 'detail'));
        $this->assertCount(1, $resource->detailFields($request)->where('attribute', 'detail'));
    }

    public function test_form_only_fields_are_respected()
    {
        $user = factory(User::class)->create();
        $resource = new UserResource($user);
        $request = NovaRequest::create('/');

        $this->assertCount(0, $resource->indexFields($request)->where('attribute', 'form'));
        $this->assertCount(1, $resource->creationFields($request)->where('attribute', 'form'));
        $this->assertCount(1, $resource->updateFields($request)->where('attribute', 'form'));
        $this->assertCount(0, $resource->detailFields($request)->where('attribute', 'form'));
    }

    public function test_relationships_are_available_when_appropriate()
    {
        // Has Many...
        $user = factory(User::class)->create();
        $resource = new UserResource($user);
        $request = NovaRequest::create('/');

        $this->assertCount(0, $resource->indexFields($request)->where('attribute', 'posts'));
        $this->assertCount(0, $resource->creationFields($request)->where('attribute', 'posts'));
        $this->assertCount(0, $resource->updateFields($request)->where('attribute', 'posts'));
        $this->assertCount(1, $resource->detailFields($request)->where('attribute', 'posts'));

        // Belongs To...
        $user = factory(Post::class)->create();
        $resource = new PostResource($user);
        $request = NovaRequest::create('/');

        $this->assertCount(1, $resource->indexFields($request)->where('attribute', 'user'));
        $this->assertCount(1, $resource->creationFields($request)->where('attribute', 'user'));
        $this->assertCount(1, $resource->updateFields($request)->where('attribute', 'user'));
        $this->assertCount(1, $resource->detailFields($request)->where('attribute', 'user'));
    }

    public function test_computed_fields_are_not_available_on_forms()
    {
        $user = factory(User::class)->create();
        $resource = new UserResource($user);
        $request = NovaRequest::create('/');

        $this->assertCount(2, $resource->indexFields($request)->where('attribute', 'ComputedField'));
        $this->assertCount(0, $resource->creationFields($request)->where('attribute', 'ComputedField'));
        $this->assertCount(0, $resource->updateFields($request)->where('attribute', 'ComputedField'));
        $this->assertCount(2, $resource->detailFields($request)->where('attribute', 'ComputedField'));
    }

    public function test_uses_default_fields()
    {
        $user = factory(User::class)->create();
        $resource = new UserResource($user);
        $request = ResourceIndexRequest::create('/');

        $this->assertCount(20, $resource->availableFields($request));
    }

    public function test_uses_index_fields()
    {
        $user = factory(User::class)->create();
        $resource = new UserWithCustomFields($user);

        $request = ResourceIndexRequest::create('/');

        $this->assertSame(
            ['Index Name'],
            $resource->indexFields($request)->pluck('name')->all()
        );

        $this->assertCount(1, $resource->availableFields($request));
        $this->assertCount(1, $resource->indexFields($request));
    }

    public function test_uses_detail_fields()
    {
        $user = factory(User::class)->create();
        $resource = new UserWithCustomFields($user);

        $request = ResourceDetailRequest::create('/');

        $this->assertSame(
            ['Detail Name', 'Restricted', 'Avatar', 'Actions'],
            $resource->detailFields($request)->pluck('name')->all()
        );

        $this->assertCount(3, $resource->availableFields($request));
        $this->assertCount(4, $resource->detailFields($request));
    }

    public function test_uses_deletable_fields()
    {
        $user = factory(User::class)->create();
        $resource = new UserWithCustomFields($user);

        $request = NovaRequest::create('/');

        $this->assertSame(
            ['Avatar'],
            $resource->deletableFields($request)->pluck('name')->all()
        );

        $this->assertCount(1, $resource->deletableFields($request));
    }

    public function test_uses_downloadable_fields()
    {
        $user = factory(User::class)->create();
        $resource = new UserWithCustomFields($user);

        $request = NovaRequest::create('/');

        $this->assertSame(
            ['Avatar'],
            $resource->downloadableFields($request)->pluck('name')->all()
        );

        $this->assertCount(1, $resource->downloadableFields($request));
    }

    public function test_uses_update_fields()
    {
        $user = factory(User::class)->create();
        $resource = new UserWithCustomFields($user);

        $request = NovaRequest::create('/', 'GET', [
            'editing' => true,
            'editMode' => 'update',
        ]);

        $this->assertSame(
            ['Update Name'],
            $resource->updateFields($request)->pluck('name')->all()
        );

        $this->assertCount(1, $resource->availableFields($request));
        $this->assertCount(1, $resource->updateFields($request));
    }

    public function test_uses_create_fields()
    {
        $user = factory(User::class)->create();
        $resource = new UserWithCustomFields($user);

        $request = NovaRequest::create('/', 'GET', [
            'editing' => true,
            'editMode' => 'create',
        ]);

        $this->assertSame(
            ['Create Name', 'Nickname'],
            $resource->creationFields($request)->pluck('name')->all()
        );

        $this->assertCount(2, $resource->availableFields($request));
        $this->assertCount(2, $resource->creationFields($request));
    }

    public function test_use_field_names_as_validator_attributes()
    {
        $user = factory(User::class)->create();
        $resource = new CustomFieldNameUserResource($user);

        $request = NovaRequest::create(
            '/nova-api/users', 'POST', [], [], [], [], json_encode(['name' => null])
        );

        try {
            $resource::validateForCreation($request);
            $this->fail('ValidationException expected');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('Custom Name', $e->validator->errors()->first(), 'Attribute name not found');
        }
    }

    public function test_resource_can_verify_relatable_field()
    {
        $user = factory(User::class)->create();
        $resource = new UserResource($user);
        $request = NovaRequest::create('/');

        $this->assertTrue($resource->hasRelatableField($request, 'roles'));
        $this->assertTrue($resource->hasRelatableField($request, 'posts'));
        $this->assertTrue($resource->hasRelatableField($request, 'actions'));
        $this->assertFalse($resource->hasRelatableField($request, 'delete'));
        $this->assertFalse($resource->hasRelatableField($request, 'forceDelete'));
        $this->assertFalse($resource->hasRelatableField($request, 'restore'));
        $this->assertFalse($resource->hasRelatableField($request, 'get'));
    }
}
