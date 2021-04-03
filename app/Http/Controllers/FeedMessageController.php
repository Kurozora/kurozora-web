<?php

namespace App\Http\Controllers;

use App\Enums\FeedVoteType;
use App\Models\FeedMessage;
use App\Helpers\JSONResult;
use App\Http\Requests\FeedMessageRepliesRequest;
use App\Http\Resources\FeedMessageResource;
use App\Models\User;
use Auth;
use Illuminate\Http\JsonResponse;

class FeedMessageController extends Controller
{
    /**
     * Get the feed message details.
     *
     * @param FeedMessage $feedMessage
     * @return JsonResponse
     */
    function details(FeedMessage $feedMessage)
    {
        return JSONResult::success([
            'data' => FeedMessageResource::collection([$feedMessage])
        ]);
    }

    /**
     * Get the replies of the feed message.
     *
     * @param FeedMessageRepliesRequest $request
     * @param FeedMessage $feedMessage
     * @return JsonResponse
     */
    function replies(FeedMessageRepliesRequest $request, FeedMessage $feedMessage) {
        $data = $request->validated();

        // Get the feed message replies
        $feedMessageReplies = $feedMessage->replies()
            ->orderByDesc('created_at')
            ->paginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $feedMessageReplies->nextPageUrl());

        return JSONResult::success([
            'data' => FeedMessageResource::collection($feedMessageReplies),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Heart a feed message.
     *
     * @param FeedMessage $feedMessage
     * @return JsonResponse
     */
    function heart(FeedMessage $feedMessage)
    {
        /** @var User $user */
        $user = Auth::user();

        // Get the vote
        $voteAction = $user->toggleHeart($feedMessage);

        // Show successful response
        return JSONResult::success([
            'data' => [
                'isHearted' => $voteAction == FeedVoteType::Heart
            ]
        ]);
    }
}
