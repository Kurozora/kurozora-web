<?php

namespace App\Http\Controllers;

use App\FeedMessage;
use App\Helpers\JSONResult;
use App\Http\Requests\GetFeedMessagesExploreRequest;
use App\Http\Requests\GetFeedMessagesHomeRequest;
use App\Http\Requests\PostFeedRequest;
use App\Http\Resources\FeedMessageResource;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class FeedController extends Controller
{
    /**
     * Post a new message to the feed.
     *
     * @param PostFeedRequest $request
     * @return JsonResponse
     */
    public function post(PostFeedRequest $request)
    {
        $data = $request->validated();

        /** @var User $user */
        $user = Auth::user();

        // Get the ID of the feed message we are replying to
        $replyingToID = null;

        if($request->has('parent_id')) {
            /** @var FeedMessage $parent */
            $parent = FeedMessage::find($data['parent_id']);

            $replyingToID = $parent->id;
        }

        // Create the feed message
        $feedMessage = $user->feedMessages()->create([
            'parent_feed_message_id'    => $replyingToID,
            'body'                      => $request->input('body'),
            'is_nsfw'                   => $data['is_nsfw'],
            'is_spoiler'                => $data['is_spoiler']
        ]);

        return JSONResult::success([
            'data' => FeedMessageResource::collection([$feedMessage]),
        ]);
    }

    /**
     * Returns the user's personal feed.
     *
     * @param GetFeedMessagesHomeRequest $request
     * @return JsonResponse
     */
    function home(GetFeedMessagesHomeRequest $request): JsonResponse
    {
        $data = $request->validated();

        /** @var User $user */
        $user = Auth::user();

        // Get the user IDs of all the users that should appear on the user's personal feed.
        $userIDs = $user->following()
            ->pluck(User::TABLE_NAME . '.id')
            ->add($user->id);

        // Get paginated feed messages that are not a reply
        $feed = FeedMessage::whereIn('user_id', $userIDs)
            ->noReplies()
            ->paginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $feed->nextPageUrl());

        return JSONResult::success([
            'data' => FeedMessageResource::collection($feed),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns the global feed.
     *
     * @param GetFeedMessagesExploreRequest $request
     * @return JsonResponse
     */
    function explore(GetFeedMessagesExploreRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get paginated global feed messages that are not a reply
        $feed = FeedMessage::noReplies()
            ->paginate($data['limit'] ?? 25);;

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $feed->nextPageUrl());

        return JSONResult::success([
            'data' => FeedMessageResource::collection($feed),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }
}
