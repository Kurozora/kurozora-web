<?php

namespace App\Http\Controllers;

use App\ForumReply;
use App\Helpers\JSONResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ForumReplyController extends Controller
{
    /**
     * Leaves a vote for a reply.
     *
     * @param Request $request
     * @param ForumReply $reply
     * @return JsonResponse
     */
    public function vote(Request $request, ForumReply $reply) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'vote'      => 'bail|required|numeric|min:0|max:1'
        ]);

        // Check validator
        if($validator->fails())
            return JSONResult::error($validator->errors()->first());

        // Get the user
        $user = Auth::user();

        // Get the vote
        $votePositive = $request->input('vote');

        // Perform the action
        if($votePositive)
            $user->toggleLike($reply);
        else
            $user->toggleDislike($reply);

        // Show successful response
        return JSONResult::success([
            'action' => $user->likeAction($reply)
        ]);
    }
}
