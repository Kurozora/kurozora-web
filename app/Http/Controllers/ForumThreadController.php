<?php

namespace App\Http\Controllers;

use App\Enums\ForumOrderType;
use App\Enums\ForumsVoteType;
use App\Models\ForumReply;
use App\Models\ForumSectionBan;
use App\Models\ForumThread;
use App\Helpers\JSONResult;
use App\Http\Requests\GetRepliesRequest;
use App\Http\Requests\LockThreadRequest;
use App\Http\Requests\PostReplyRequest;
use App\Http\Requests\SearchThreadRequest;
use App\Http\Requests\VoteThreadRequest;
use App\Http\Resources\ForumReplyResource;
use App\Http\Resources\ForumThreadResource;
use Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

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
        $voteType = ForumsVoteType::fromValue((int) $request->input('vote'));
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
     * @throws AuthorizationException
     * @throws ConflictHttpException
     * @throws TooManyRequestsHttpException
     */
    public function postReply(PostReplyRequest $request, ForumThread $thread): JsonResponse
    {
        // Check if the user is banned
        $foundBan = ForumSectionBan::getBanInfo(Auth::id(), $thread->section_id);

        if ($foundBan !== null)
            throw new AuthorizationException($foundBan['message']);

        // Get variables
        $givenContent = $request->input('content');

        // Check if the thread is not locked
        if ($thread->locked)
            throw new ConflictHttpException('You are not allowed to post in a locked thread.');

        // Check if the user has already posted within the cooldown period
        if (ForumReply::testPostCooldown(Auth::id()))
            throw new TooManyRequestsHttpException(ForumReply::COOLDOWN_POST_REPLY,'You can only post a reply once every ' . ForumReply::COOLDOWN_POST_REPLY . ' seconds.');

        // Create the reply
        $newReply = ForumReply::create([
            'thread_id'     => $thread->id,
            'user_id'       => Auth::id(),
            'ip_address'    => $request->ip(),
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
        $data = $request->validated();

        // Get the replies
        /** @var ForumReply $replies */
        $replies = $thread->replies();
        $forumOrder = ForumOrderType::fromValue($data['order']);

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
                $replies->orderBy('created_at');
                break;
            case ForumOrderType::Poor:
                $replies->joinReactionCounterOfType('Like')
                        ->orderBy('reaction_like_count');
                break;
            case ForumOrderType::Controversial:
                $replies->joinReactionTotal()
                        ->orderBy('reaction_total_weight');
                break;
        }

        // Paginate the replies
        $replies = $replies->paginate($data['limit'] ?? 25);

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
        $threads = ForumThread::kSearch($searchQuery, [
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
        if ($thread->locked != $doLock) {
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
