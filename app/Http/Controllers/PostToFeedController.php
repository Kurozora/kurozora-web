<?php

namespace App\Http\Controllers;

use App\FeedMessage;
use App\Helpers\JSONResult;
use App\Http\Requests\PostToFeedRequest;

class PostToFeedController extends Controller
{
    public function post(PostToFeedRequest $request)
    {
        // Get the ID of the feed message we are replying to
        $replyingToID = null;

        if($request->has('in_reply_to')) {
            $parent = FeedMessage::find($request->input('in_reply_to'));

            if($parent->isReply())
                return JSONResult::error('You can only reply to top-level feed messages.');

            $replyingToID = $parent->id;
        }


        return JSONResult::success();
    }
}
