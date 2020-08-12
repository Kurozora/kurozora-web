<?php

namespace App\Http\Controllers;

use App\Enums\ForumOrderType;
use App\Enums\VoteType;
use App\ForumReply;
use App\ForumSectionBan;
use App\ForumThread;
use App\Helpers\JSONResult;
use App\Http\Requests\GetRepliesRequest;
use App\Http\Requests\LockThreadRequest;
use App\Http\Requests\PostReplyRequest;
use App\Http\Requests\SearchThreadRequest;
use App\Http\Requests\VoteThreadRequest;
use App\Http\Resources\ForumReplyResource;
use App\Http\Resources\ForumThreadResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ForumThreadController extends Controller
{
    /**
     * Get thread details
     *
     * @param ForumThread $thread
     * @return JsonResponse
     */
    public function details(ForumThread $thread): JsonResponse
    {
        return JSONResult::success([
            'data' => ForumThreadResource::collection([$thread])
        ]);
    }

    /**
     * Vote for a thread
     *
     * @param VoteThreadRequest $request
     * @param ForumThread $thread
     *
     * @return JsonResponse
     */
    public function vote(VoteThreadRequest $request, ForumThread $thread): JsonResponse
    {
        // Get the user
        $user = Auth::user();

        // Get the vote
        $voteType = VoteType::fromValue((int) $request->input('vote'));
        $voteAction = $user->toggleVote($thread, $voteType);

        // Show successful response
        return JSONResult::success([
            'data' => [
                'voteAction' => $voteAction
            ]
        ]);
    }

    /**
     * Allows the user to submit a reply to a thread
     *
     * @param PostReplyRequest $request
     * @param ForumThread $thread
     * @return JsonResponse
     */
    public function postReply(PostReplyRequest $request, ForumThread $thread): JsonResponse
    {
        // Check if the user is banned
        $foundBan = ForumSectionBan::getBanInfo(Auth::id(), $thread->section_id);

        if($foundBan !== null)
            return JSONResult::error($foundBan['message']);

        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'content' => 'bail|required|min:1'
        ]);

        // Check validator
        if($validator->fails())
            return JSONResult::error($validator->errors()->first());

        // Get variables
        $givenContent = $request->input('content');

        // Check if the thread is not locked
        if($thread->locked)
            return JSONResult::error('You cannot post in this thread.');

        // Check if the user has already posted within the cooldown period
        if(ForumReply::testPostCooldown(Auth::id()))
            return JSONResult::error('You can only post a reply once every ' . ForumReply::COOLDOWN_POST_REPLY . ' seconds.');

        // Create the reply
        $newReply = ForumReply::create([
            'thread_id'     => $thread->id,
            'user_id'       => Auth::id(),
            'ip'            => $request->ip(),
            'content'       => $givenContent
        ]);

        return JSONResult::success([
            'data' => ForumReplyResource::collection([$newReply])
        ]);
    }

    /**
     * Vote for a thread.
     *
     * @param GetRepliesRequest $request
     * @param ForumThread $thread
     * @return JsonResponse
     */
    public function replies(GetRepliesRequest $request, ForumThread $thread): JsonResponse
    {
        // Fetch the variables
        $givenOrder = $request->input('order');

        // Get the replies
        /** @var ForumReply $replies */
        $replies = $thread->replies();
        $forumOrder = ForumOrderType::fromValue($givenOrder);

        switch ($forumOrder) {
            case ForumOrderType::Best:
                $replies->joinReactionTotal()
                        ->orderBy('reaction_total_weight', 'desc');
                break;
            case ForumOrderType::Top:
                $replies->joinReactionCounterOfType('Like')
                        ->orderBy('reaction_like_count', 'desc');
                break;
            case ForumOrderType::New:
                $replies->orderBy('created_at', 'desc');
                break;
            case ForumOrderType::Old:
                $replies->orderBy('created_at', 'asc');
                break;
            case ForumOrderType::Poor:
                $replies->joinReactionCounterOfType('Like')
                        ->orderBy('reaction_like_count', 'asc');
                break;
            case ForumOrderType::Controversial:
                $replies->joinReactionTotal()
                        ->orderBy('reaction_total_weight', 'asc');
                break;
        }

        // Paginate the replies
        $replies = $replies->paginate(ForumThread::REPLIES_PER_PAGE);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $replies->nextPageUrl());

        // Show successful response
        return JSONResult::success([
            'data' => ForumReplyResource::collection($replies),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Retrieves ForumThread search results
     *
     * @param SearchThreadRequest $request
     * @return JsonResponse
     */
    public function search(SearchThreadRequest $request): JsonResponse
    {
        $searchQuery = $request->input('query');

        // Search for the thread
        $threads = ForumThread::kuroSearch($searchQuery, [
            'limit' => ForumThread::MAX_SEARCH_RESULTS
        ]);

        // Show response
        return JSONResult::success([
            'data' => ForumThreadResource::collection($threads)
        ]);
    }

    /**
     * Lock or unlock a thread
     *
     * @param LockThreadRequest $request
     * @param ForumThread $thread
     * @return JsonResponse
     */
    function lock(LockThreadRequest $request, ForumThread $thread): JsonResponse
    {
        // Lock or unlock the thread
        $doLock = (bool) $request->input('lock');

        // If the action is different that the current lock state
        if($thread->locked != $doLock) {
            $thread->locked = $doLock;
            $thread->save();
        }

        return JSONResult::success([
            'data' => [
                'isLocked' => (bool) $thread->locked
            ]
        ]);
    }
}
