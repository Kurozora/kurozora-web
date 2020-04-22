<?php

namespace App\Http\Controllers;

use App\FeedMessage;
use App\Helpers\JSONResult;
use App\Http\Requests\PostToFeedRequest;
use App\User;
use Auth;

class PostToFeedController extends Controller
{
    public function post(PostToFeedRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // Get the ID of the feed message we are replying to
        $replyingToID = null;

        if($request->has('in_reply_to')) {
            /** @var FeedMessage $parent */
            $parent = FeedMessage::find($request->input('in_reply_to'));

            if($parent->isReply())
                return JSONResult::error('You can only reply to top-level feed messages.');

            $replyingToID = $parent->id;
        }

        // Create the feed message
        $user->feedMessages()->create([
            'parent_feed_message_id'    => $replyingToID,
            'body'                      => $request->input('body')
        ]);

        return JSONResult::success();
    }
}
