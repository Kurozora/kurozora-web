<?php

namespace App\Http\Controllers;

use App\ForumReply;
use App\ForumReplyVote;
use App\Helpers\JSONResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ForumReplyController extends Controller
{
    /**
     * Leaves a vote for a reply
     *
     * @param Request $request
     * @param $replyID
     */
    public function vote(Request $request, $replyID) {
        // Get the reply
        $reply = ForumReply::find($replyID);

        // Reply does not exist
        if(!$reply)
            (new JSONResult())->setError(JSONResult::ERROR_FORUM_REPLY_NON_EXISTENT)->show();

        // User tried to vote for their own reply
        if($reply->user_id == $request->user_id)
            (new JSONResult())->setError('You can not vote for your own replies.')->show();

        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'vote'      => 'bail|required|numeric|min:0|max:1'
        ]);

        // Check validator
        if($validator->fails())
            (new JSONResult())->setError($validator->errors()->first())->show();

        // Get the vote
        $givenVote = $request->input('vote');

        // Check if they've voted already
        $foundVote = ForumReplyVote::where([
            ['user_id',     '=', $request->user_id],
            ['reply_id',    '=', $reply->id]
        ])->first();

        // They haven't voted for this reply yet
        if($foundVote == null) {
            // Insert vote
            ForumReplyVote::create([
                'user_id'   => $request->user_id,
                'reply_id'  => $reply->id,
                'positive'  => $givenVote
            ]);
        }
        else {
            // Modify the vote
            if($foundVote->positive != $givenVote) {
                $foundVote->positive = $givenVote;
                $foundVote->save();
            }
        }

        // Show successful response
        (new JSONResult())->show();
    }
}
