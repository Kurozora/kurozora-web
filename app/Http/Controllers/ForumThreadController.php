<?php

namespace App\Http\Controllers;

use App\ForumReply;
use App\ForumSectionBan;
use App\ForumThread;
use App\Helpers\JSONResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ForumThreadController extends Controller
{
    /**
     * Get thread information
     *
     * @param ForumThread $thread
     */
    public function threadInfo(ForumThread $thread) {
        $thread->load('user', 'replies');

        // Show thread information
        (new JSONResult())->setData([
            'thread' => [
                'id' => $thread->id,
                'title' => $thread->title,
                'content' => $thread->content,
                'locked' => (bool) $thread->locked,
                'creation_date' => $thread->created_at->format('Y-m-d H:i:s'),
                'reply_count' => $thread->replies->count(),
                'score' => $thread->likesDiffDislikesCount,
                'user' => [
                    'id' => $thread->user->id,
                    'username' => $thread->user->username
                ]
            ]
        ])->show();
    }

    /**
     * Vote for a thread
     *
     * @param Request $request
     * @param ForumThread $thread
     */
    public function vote(Request $request, ForumThread $thread) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'vote'      => 'bail|required|numeric|min:0|max:1'
        ]);

        // Check validator
        if($validator->fails())
            (new JSONResult())->setError($validator->errors()->first())->show();

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
        (new JSONResult())->setData([
            'action' => $user->likeAction($thread)
        ])->show();
    }

    /**
     * Allows the user to submit a reply to a thread
     *
     * @param Request $request
     * @param ForumThread $thread
     */
    public function postReply(Request $request, ForumThread $thread) {
        // Check if the user is banned
        $foundBan = ForumSectionBan::getBanInfo($request->user_id, $thread->section_id);

        if($foundBan !== null)
            (new JSONResult())->setError($foundBan['message'])->show();

        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'content'   => 'bail|required|min:' . ForumThread::MIN_CONTENT_LENGTH
        ]);

        // Check validator
        if($validator->fails())
            (new JSONResult())->setError($validator->errors()->first())->show();

        // Get variables
        $givenContent = $request->input('content');

        // Check if the thread is not locked
        if($thread->locked)
            (new JSONResult())->setError(JSONResult::ERROR_CANNOT_POST_IN_THREAD)->show();

        // Check if the user has already posted within the cooldown period
        if(ForumReply::testPostCooldown($request->user_id))
            (new JSONResult())->setError('You can only post a reply once every ' . ForumReply::COOLDOWN_POST_REPLY . ' seconds.')->show();

        // Create the reply
        $newReply = ForumReply::create([
            'thread_id'     => $thread->id,
            'user_id'       => $request->user_id,
            'ip'            => $request->ip(),
            'content'       => $givenContent
        ]);

        (new JSONResult())->setData(['reply_id' => $newReply->id])->show();
    }

    /**
     * Vote for a thread
     *
     * @param Request $request
     * @param ForumThread $thread
     */
    public function replies(Request $request, ForumThread $thread) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'order'         => 'bail|required|in:top,recent',
            'page'          => 'bail|numeric|min:0'
        ]);

        // Fetch the variables
        $givenOrder = $request->input('order');
        $givenPage = $request->input('page');

        // Check validator
        if($validator->fails())
            (new JSONResult())->setError($validator->errors()->first())->show();

        // Get the replies
        $replies = $thread->replies();

        if($givenOrder == 'recent')
            $replies = $replies->orderBy('created_at', 'DESC');
        else if($givenOrder == 'top')
            $replies = $replies->orderByLikesCount();

        $replies = $replies->paginate(ForumThread::REPLIES_PER_PAGE);

        // Format the replies
        $displayReplies = [];

        foreach($replies as $reply) {
            $displayReplies[] = [
                'id'        => $reply->id,
                'posted_at' => $reply->created_at->format('Y-m-d H:i:s'),
                'user' => [
                    'id'        => $reply->user->id,
                    'username'  => $reply->user->username,
                    'avatar'    => $reply->user->getAvatarURL()
                ],
                'score'     => $reply->likesDiffDislikesCount,
                'content'   => $reply->content
            ];
        }

        // Show successful response
        (new JSONResult())->setData([
            'page'          => $givenPage,
            'reply_pages'   => $thread->getPageCount(),
            'replies'       => $displayReplies
        ])->show();
    }

    /**
     * Retrieves Anime search results
     *
     * @param Request $request
     */
    public function search(Request $request) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'query' => 'bail|required|string|min:1'
        ]);

        // Check validator
        if($validator->fails())
            (new JSONResult())->setError($validator->errors()->first())->show();

        $searchQuery = $request->input('query');

        // Search for the thread
        $resultArr = ForumThread::kuroSearch($searchQuery, [
            'limit' => ForumThread::MAX_SEARCH_RESULTS
        ]);

        // Format the results
        $displayResults = [];

        foreach($resultArr as $thread) {
            $displayResults[] = [
                'id'                => $thread->id,
                'title'             => $thread->title,
                'content_teaser'    =>
                    substr(strip_tags($thread->content), 0, 100) .
                    ((strlen($thread->content) > 100) ? '...' : '')
                ,
                'locked'            => (bool) $thread->locked
            ];
        }

        // Show response
        (new JSONResult())->setData([
            'max_search_results'    => ForumThread::MAX_SEARCH_RESULTS,
            'results'               => $displayResults
        ])->show();
    }

    /**
     * Lock or unlock a thread
     *
     * @param Request $request
     * @param ForumThread $thread
     */
    function lock(Request $request, ForumThread $thread) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'lock' => 'bail|required|numeric|min:0|max:1'
        ]);

        // Check validator
        if($validator->fails())
            (new JSONResult())->setError($validator->errors()->first())->show();

        // Lock or unlock the thread
        $doLock = (bool) $request->input('lock');

        // If the action is different that the current lock state
        if($thread->locked != $doLock) {
            $thread->locked = $doLock;
            $thread->save();
        }

        (new JSONResult())->setData([
            'thread' => [
                'locked' => (bool) $thread->locked
            ]
        ])->show();
    }
}
