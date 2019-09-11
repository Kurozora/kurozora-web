<?php

namespace App\Http\Controllers;

use App\ForumReply;
use App\ForumSectionBan;
use App\ForumThread;
use App\Helpers\CollectionLikeChecker;
use App\Helpers\JSONResult;
use App\Http\Resources\ForumReplyResource;
use App\Http\Resources\ForumThreadResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ForumThreadController extends Controller
{
    /**
     * Get thread information
     *
     * @param ForumThread $thread
     * @return JsonResponse
     */
    public function threadInfo(ForumThread $thread) {
        $thread->load('user', 'replies');

        // Show thread information
        return JSONResult::success([
            'thread' => [
                'id'            => $thread->id,
                'title'         => $thread->title,
                'content'       => $thread->content,
                'locked'        => (bool) $thread->locked,
                'creation_date' => $thread->created_at->format('Y-m-d H:i:s'),
                'reply_count'   => $thread->replies->count(),
                'score'         => $thread->likesDiffDislikesCount,
                'user'          => [
                    'id'        => $thread->user->id,
                    'username'  => $thread->user->username
                ]
            ]
        ]);
    }

    /**
     * Vote for a thread
     *
     * @param Request $request
     * @param ForumThread $thread
     * @return JsonResponse
     */
    public function vote(Request $request, ForumThread $thread) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'vote'      => 'bail|required|numeric|min:0|max:1'
        ]);

        // Check validator
        if($validator->fails())
            return JSONResult::error(($validator->errors()->first()));

        // Get the user
        $user = Auth::user();

        // Get the vote
        $votePositive = $request->input('vote');

        // Perform the action
        if($votePositive)
            $user->toggleLike($thread);
        else
            $user->toggleDislike($thread);

        // Show successful response
        return JSONResult::success([
            'action' => $user->likeAction($thread)
        ]);
    }

    /**
     * Allows the user to submit a reply to a thread
     *
     * @param Request $request
     * @param ForumThread $thread
     * @return JsonResponse
     */
    public function postReply(Request $request, ForumThread $thread) {
        // Check if the user is banned
        $foundBan = ForumSectionBan::getBanInfo(Auth::id(), $thread->section_id);

        if($foundBan !== null)
            return JSONResult::error($foundBan['message']);

        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'content'   => 'bail|required|min:1'
        ]);

        // Check validator
        if($validator->fails())
            return JSONResult::error($validator->errors()->first());

        // Get variables
        $givenContent = $request->input('content');

        // Check if the thread is not locked
        if($thread->locked)
            return JSONResult::error(JSONResult::ERROR_CANNOT_POST_IN_THREAD);

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
            'reply_id' => $newReply->id
        ]);
    }

    /**
     * Vote for a thread.
     *
     * @param Request $request
     * @param ForumThread $thread
     * @return JsonResponse
     */
    public function replies(Request $request, ForumThread $thread) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'order'         => 'bail|required|in:top,recent'
        ]);

        // Fetch the variables
        $givenOrder = $request->input('order');

        // Check validator
        if($validator->fails())
            return JSONResult::error($validator->errors()->first());

        // Get the replies
        $replies = $thread->replies();

        if($givenOrder == 'recent')
            $replies = $replies->orderBy('created_at', 'DESC');
        else if($givenOrder == 'top')
            $replies = $replies->orderByLikesCount();

        // Paginate the replies
        $replies = $replies->paginate(ForumThread::REPLIES_PER_PAGE);

        // Show successful response
        return JSONResult::success([
            'page'          => $replies->currentPage(),
            'last_page'     => $replies->lastPage(),
            'replies'       => ForumReplyResource::collection($replies)
        ]);
    }

    /**
     * Retrieves Anime search results
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'query' => 'bail|required|string|min:1'
        ]);

        // Check validator
        if($validator->fails())
            return JSONResult::error($validator->errors()->first());

        $searchQuery = $request->input('query');

        // Search for the thread
        $threads = ForumThread::kuroSearch($searchQuery, [
            'limit' => ForumThread::MAX_SEARCH_RESULTS
        ]);

        // Show response
        return JSONResult::success([
            'max_search_results'    => ForumThread::MAX_SEARCH_RESULTS,
            'results'               => ForumThreadResource::collection($threads)
        ]);
    }

    /**
     * Lock or unlock a thread
     *
     * @param Request $request
     * @param ForumThread $thread
     * @return JsonResponse
     */
    function lock(Request $request, ForumThread $thread) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'lock' => 'bail|required|numeric|min:0|max:1'
        ]);

        // Check validator
        if($validator->fails())
            return JSONResult::error($validator->errors()->first());

        // Lock or unlock the thread
        $doLock = (bool) $request->input('lock');

        // If the action is different that the current lock state
        if($thread->locked != $doLock) {
            $thread->locked = $doLock;
            $thread->save();
        }

        return JSONResult::success([
            'thread' => [
                'locked' => (bool) $thread->locked
            ]
        ]);
    }
}
