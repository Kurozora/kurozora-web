<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Requests\GetFeedMessagesExploreRequest;
use App\Http\Requests\GetFeedMessagesHomeRequest;
use App\Http\Requests\PostFeedRequest;
use App\Http\Resources\FeedMessageResource;
use App\Models\FeedMessage;
use App\Models\User;
use App\Notifications\NewFeedMessageReply;
use App\Notifications\NewFeedMessageReShare;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

class FeedController extends Controller
{
    /**
     * Post a new message to the feed.
     *
     * @param PostFeedRequest $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function post(PostFeedRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get the auth user
        $user = auth()->user();

        // Check if the message has already been re-shared as user is allowed only one re-share per message
        if ($data['is_reshare'] ?? false) {
            $reShareExists = FeedMessage::where('parent_feed_message_id', '=', $data['parent_id'])
                ->where('user_id', $user->id)
                ->where('is_reshare', true)
                ->exists();

            if ($reShareExists) {
                throw new AuthorizationException('You are not allowed to re-share a message more than once.');
            }
        }

        // Create the feed message
        $feedMessage = $user->feed_messages()->create([
            'parent_feed_message_id'    => $data['parent_id'] ?? null,
            'body'                      => $request->input('body'),
            'is_reply'                  => $data['is_reply'] ?? false,
            'is_reshare'                => $data['is_reshare'] ?? false,
            'is_nsfw'                   => $data['is_nsfw'] ?? false,
            'is_spoiler'                => $data['is_spoiler'] ?? false,
        ]);

        if ($data['is_reply'] ?? false) {
            // Get parent message
            $parentMessage = FeedMessage::firstWhere('id', '=', $data['parent_id']);

            // Notify user of the reply if the message doesn't belong to the current user
            if ($parentMessage->user->id != $user->id) {
                $parentMessage->user->notify(new NewFeedMessageReply($feedMessage));
            }
        } else if ($data['is_reshare'] ?? false) {
            // Get parent message
            $parentMessage = FeedMessage::firstWhere('id', '=', $data['parent_id']);

            // // Notify user of the re-share if the message doesn't belong to the current user
            if ($parentMessage->user->id != $user->id) {
                $parentMessage->user->notify(new NewFeedMessageReShare($feedMessage));
            }
        }

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

        // Get the auth user
        $user = auth()->user();

        // Get the user IDs of all the users that should appear on the user's personal feed.
        $userIDs = $user->following()
            ->pluck(User::TABLE_NAME . '.id')
            ->add($user->id);

        // Get paginated feed messages that are not a reply
        $feed = FeedMessage::noReplies()
            ->with([
                'loveReactant.reactions.reacter.reacterable',
                'loveReactant.reactionCounters',
            ])
            ->whereIn('user_id', $userIDs)
            ->orderByDesc('created_at')
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
            ->with([
                'loveReactant.reactions.reacter.reacterable',
                'loveReactant.reactionCounters',
            ])
            ->orderByDesc('created_at')
            ->paginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $feed->nextPageUrl());

        return JSONResult::success([
            'data' => FeedMessageResource::collection($feed),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }
}
