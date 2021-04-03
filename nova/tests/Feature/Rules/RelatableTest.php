<?php

namespace Laravel\Nova\Tests\Feature\Rules;

use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Rules\Relatable;
use Laravel\Nova\Tests\Fixtures\Post;
use Laravel\Nova\Tests\Fixtures\PostResource;
use Laravel\Nova\Tests\Fixtures\User;
use Mockery as m;

trait RelatableTest
{
    public function test_validation_can_ignore_query_with_count()
    {
        $user = factory(User::class)->create();
        $post = factory(Post::class)->create([
            'user_id' => $user->id,
        ]);

        $request = m::mock(NovaRequest::class);

        $request->shouldReceive('newResource')->andReturn(new PostResource($post))
            ->shouldReceive('model')->andReturn($post)
            ->shouldReceive('isResourceIndexRequest')->andReturn(false)
            ->shouldReceive('isResourceDetailRequest')->andReturn(false)
            ->shouldReceive('isCreateOrAttachRequest')->andReturn(false)
            ->shouldReceive('isUpdateOrUpdateAttachedRequest')->andReturn(true);

        $query = User::withCount('posts')->orderBy('post_count', 'desc');

        $validation = new Relatable($request, $query);

        $this->assertTrue($validation->passes('user_id', $user->id));
    }
}
