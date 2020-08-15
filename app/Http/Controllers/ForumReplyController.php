<?php

namespace App\Http\Controllers;

use App\Enums\VoteType;
use App\ForumReply;
use App\Helpers\JSONResult;
use App\Http\Requests\VoteReplyRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ForumReplyController extends Controller
{
    /**
     * Leaves a vote for a reply.
     *
     * @param VoteReplyRequest $request
     * @param ForumReply $reply
     * @return JsonResponse
     */
    public function vote(VoteReplyRequest $request, ForumReply $reply): JsonResponse
    {
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
