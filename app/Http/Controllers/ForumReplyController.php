<?php

namespace App\Http\Controllers;

use App\Enums\VoteType;
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
    public function vote(Request $request, ForumReply $reply): JsonResponse
    {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'vote'      => 'bail|required|numeric|in:-1,1'
        ]);

        // Check validator
        if($validator->fails())
            return JSONResult::error($validator->errors()->first());

        // Get the user
        $user = Auth::user();

        // Get the vote
        $voteType = VoteType::fromValue((int) $request->input('vote'));
        $voteAction = $user->toggleVote($reply, $voteType);

        // Show successful response
        return JSONResult::success([
            'data' => [
                'voteAction' => $voteAction
            ]
        ]);
    }
}
