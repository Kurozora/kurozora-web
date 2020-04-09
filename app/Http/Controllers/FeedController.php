<?php

namespace App\Http\Controllers;

use App\FeedMessage;
use App\Helpers\JSONResult;
use App\Http\Resources\FeedMessageResource;
use App\User;
use Illuminate\Support\Facades\Auth;

class FeedController extends Controller
{
    /**
     * Returns the user's personal feed.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    function personal()
    {
        $user = Auth::user();

        /*
         * Get the user IDs of all the users that should appear ..
         * .. on the user's personal feed.
         */
        $userIDs = $user->following()
            ->pluck(User::TABLE_NAME . '.id')
            ->add($user->id);

        // Get all the relevant feed messages that are not a reply
        $feed = FeedMessage::whereIn('user_id', $userIDs)
            ->noReplies()
            ->get();

        return $this->response($feed);
    }

    /**
     * Returns the global feed.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    function global()
    {
        // Get all the relevant global feed messages that are not a reply
        $feed = FeedMessage::noReplies()->get();

        return $this->response($feed);
    }

    /**
     * Returns the response for feeds.
     *
     * @param FeedMessage[] $feed
     * @return \Illuminate\Http\JsonResponse
     */
    private function response($feed)
    {
        return JSONResult::success([
            'messages' => FeedMessageResource::collection($feed)
        ]);
    }
}
