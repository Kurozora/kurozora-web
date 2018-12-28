<?php

namespace App\Http\Controllers;

use App\ForumReply;
use App\ForumReplyVote;
use App\ForumSectionBan;
use App\ForumThread;
use App\ForumThreadVote;
use App\Helpers\JSONResult;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ForumThreadController extends Controller
{
    /**
     * Get thread information
     *
     * @param $threadID
     */
    public function threadInfo($threadID) {
        // Find the thread
        $thread = ForumThread::find($threadID);

        // Thread wasn't found
        if(!$thread)
            (new JSONResult())->setError(JSONResult::ERROR_FORUM_THREAD_NON_EXISTENT)->show();

        // Show thread information
        (new JSONResult())->setData([
            'thread' => $thread->formatForDetailsResponse()
        ])->show();
    }

    /**
     * Vote for a thread
     *
     * @param Request $request
     * @param $threadID
     */
    public function vote(Request $request, $threadID) {
        // Find the thread
        $thread = ForumThread::find($threadID);

        // Thread wasn't found
        if(!$thread)
            (new JSONResult())->setError(JSONResult::ERROR_FORUM_THREAD_NON_EXISTENT)->show();

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
        $foundVote = ForumThreadVote::where([
            ['user_id',     '=', $request->user_id],
            ['thread_id',   '=', $threadID]
        ])->first();

        // They haven't voted for this thread yet
        if($foundVote == null) {
            // Insert vote
            ForumThreadVote::create([
                'user_id'   => $request->user_id,
                'thread_id' => $threadID,
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

    /**
     * Allows the user to submit a reply to a thread
     *
     * @param Request $request
     * @param $threadID
     */
    public function postReply(Request $request, $threadID) {
        // Find the thread
        $thread = ForumThread::find($threadID);

        // Thread wasn't found
        if(!$thread)
            (new JSONResult())->setError(JSONResult::ERROR_FORUM_THREAD_NON_EXISTENT)->show();

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
     * @param $threadID
     */
    public function replies(Request $request, $threadID) {
        // Find the thread
        $thread = ForumThread::find($threadID);

        // Thread wasn't found
        if(!$thread)
            (new JSONResult())->setError(JSONResult::ERROR_FORUM_THREAD_NON_EXISTENT)->show();

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

        // Determine columns to select
        $columnsToSelect = [
            ForumReply::TABLE_NAME . '.id AS reply_id',
            ForumReply::TABLE_NAME . '.content AS content',
            User::TABLE_NAME . '.username AS username',
            User::TABLE_NAME . '.id AS user_id',
            User::TABLE_NAME . '.avatar AS user_avatar',
            ForumReply::TABLE_NAME . '.created_at AS creation_date',
            // Select the upvote count via subquery
            DB::raw('(SELECT COUNT(*) FROM ' . ForumReplyVote::TABLE_NAME . ' WHERE reply_id = ' . ForumReply::TABLE_NAME . '.id AND positive = 1) AS upvote_count'),
            // Select the downvote count via subquery
            DB::raw('(SELECT COUNT(*) FROM ' . ForumReplyVote::TABLE_NAME . ' WHERE reply_id = ' . ForumReply::TABLE_NAME . '.id AND positive = 0) AS downvote_count')
        ];

        // Create query
        $replyInfo = DB::table(ForumReply::TABLE_NAME)
            ->select($columnsToSelect)
            ->join(User::TABLE_NAME, function ($join) {
                $join->on(ForumReply::TABLE_NAME . '.user_id', '=', User::TABLE_NAME . '.id');
            })
            ->where([
                [ForumReply::TABLE_NAME . '.thread_id', '=', $threadID]
            ]);

        // Add order
        if($givenOrder == 'top')
            $replyInfo->orderBy('upvote_count', 'DESC');
        else if($givenOrder == 'recent')
            $replyInfo->orderBy('creation_date', 'DESC');

        if($givenPage == null)
            $givenPage = 0;

        $replyInfo->offset($givenPage * ForumThread::REPLIES_PER_PAGE);
        $replyInfo->limit(ForumThread::REPLIES_PER_PAGE);

        // Get the results
        $rawReplies = $replyInfo->get();
        $displayReplies = [];

        foreach($rawReplies as $rawReply) {
            $displayReplies[] = [
                'id'        => $rawReply->reply_id,
                'posted_at' => $rawReply->creation_date,
                'user' => [
                    'id'        => $rawReply->user_id,
                    'username'  => $rawReply->username,
                    'avatar'    => User::avatarFileToURL($rawReply->user_avatar)
                ],
                'score'     => ($rawReply->upvote_count - $rawReply->downvote_count),
                'content'   => $rawReply->content
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
        $rawSearchResults = ForumThread::search($searchQuery)->limit(ForumThread::MAX_SEARCH_RESULTS)->get();

        // Format the results
        $displayResults = [];

        foreach($rawSearchResults as $thread) {
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
}
