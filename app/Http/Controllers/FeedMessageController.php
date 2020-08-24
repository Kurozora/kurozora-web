<?php

namespace App\Http\Controllers;

use App\FeedMessage;
use App\Helpers\JSONResult;
use App\Http\Resources\FeedMessageResource;
use App\User;
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
                'voteAction' => $voteAction
            ]
        ]);
    }
}
