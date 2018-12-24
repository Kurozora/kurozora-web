<?php

namespace App\Http\Controllers;

use App\ForumReply;
use App\ForumSection;
use App\ForumSectionBan;
use App\ForumThread;
use App\ForumThreadVote;
use App\Helpers\JSONResult;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ForumSectionController extends Controller
{
    /**
     * Generates an overview of forum sections
     */
    public function overview() {
        $rawSections = ForumSection::all();

        $sections = [];

        foreach($rawSections as $rawSection)
            $sections[] = $rawSection->formatForResponse();

        (new JSONResult())->setData(['sections' => $sections])->show();
    }

    /**
     * Gets the information of a section
     *
     * @param $sectionID
     */
    public function details($sectionID) {
        // Get the section
        $section = ForumSection::find($sectionID);

        // Check if the section exists
        if(!$section)
            (new JSONResult())->setError(JSONResult::ERROR_FORUM_SECTION_NON_EXISTENT)->show();

        (new JSONResult())->setData([
            'section' => $section->formatForDetailsResponse()
        ])->show();
    }

    /**
     * Returns the threads of a section
     *
     * @param Request $request
     * @param $sectionID
     */
    public function threads(Request $request, $sectionID) {
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

        // Check if the section exists
        if(!ForumSection::where('id', $sectionID)->exists())
            (new JSONResult())->setError(JSONResult::ERROR_FORUM_SECTION_NON_EXISTENT)->show();

        // Determine columns to select
        $columnsToSelect = [
            ForumThread::TABLE_NAME . '.id AS thread_id',
            ForumThread::TABLE_NAME . '.title AS title',
            ForumThread::TABLE_NAME . '.content AS content',
            ForumThread::TABLE_NAME . '.locked AS locked',
            User::TABLE_NAME . '.username AS username',
            User::TABLE_NAME . '.id AS user_id',
            ForumThread::TABLE_NAME . '.created_at AS creation_date',
            // Select the reply count via subquery
            DB::raw('(SELECT COUNT(*) FROM ' . ForumReply::TABLE_NAME . ' WHERE thread_id = ' . ForumThread::TABLE_NAME . '.id) AS reply_count'),
            // Select the upvote count via subquery
            DB::raw('(SELECT COUNT(*) FROM ' . ForumThreadVote::TABLE_NAME . ' WHERE thread_id = ' . ForumThread::TABLE_NAME . '.id AND positive = 1) AS upvote_count'),
            // Select the downvote count via subquery
            DB::raw('(SELECT COUNT(*) FROM ' . ForumThreadVote::TABLE_NAME . ' WHERE thread_id = ' . ForumThread::TABLE_NAME . '.id AND positive = 0) AS downvote_count')
        ];

        // Create query
        $threadInfo = DB::table(ForumThread::TABLE_NAME)
            ->select($columnsToSelect)
            ->join(User::TABLE_NAME, function ($join) {
                $join->on(ForumThread::TABLE_NAME . '.user_id', '=', User::TABLE_NAME . '.id');
            })
            ->where([
                [ForumThread::TABLE_NAME . '.section_id', '=', $sectionID]
            ]);

        // Add order
        if($givenOrder == 'top')
            $threadInfo->orderBy('upvote_count', 'DESC');
        else if($givenOrder == 'recent')
            $threadInfo->orderBy('creation_date', 'DESC');

        // Add page/offset
        if($givenPage == null)
            $givenPage = 0;

        $threadInfo->offset($givenPage * ForumSection::THREADS_PER_PAGE);
        $threadInfo->limit(ForumSection::THREADS_PER_PAGE);

        // Get the results
        $rawThreads = $threadInfo->get();

        $threads = [];

        foreach($rawThreads as $rawThread)
            $threads[] = [
                'id'                => $rawThread->thread_id,
                'title'             => $rawThread->title,
                'content_teaser'    =>
                    substr(strip_tags($rawThread->content), 0, 100) .
                    ((strlen($rawThread->content) > 100) ? '...' : '')
                ,
                'locked'            => (bool) $rawThread->locked,
                'poster_user_id'    => $rawThread->user_id,
                'poster_username'   => $rawThread->username,
                'creation_date'     => $rawThread->creation_date,
                'reply_count'       => $rawThread->reply_count,
                'score'             => ($rawThread->upvote_count - $rawThread->downvote_count)
            ];

        // Show threads in response
        (new JSONResult())->setData([
            'page'      => (int) $givenPage,
            'threads'   => $threads
        ])->show();
    }

    /**
     * Allows the user to submit a new thread
     *
     * @param Request $request
     * @param $sectionID
     */
    public function postThread(Request $request, $sectionID) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'title'         => 'bail|required|min:' . ForumThread::MIN_TITLE_LENGTH,
            'content'       => 'bail|required|min:' . ForumThread::MIN_CONTENT_LENGTH
        ]);

        // Check validator
        if($validator->fails())
            (new JSONResult())->setError($validator->errors()->first())->show();

        // Check if the section exists
        if(!ForumSection::where('id', $sectionID)->exists())
            (new JSONResult())->setError(JSONResult::ERROR_FORUM_SECTION_NON_EXISTENT)->show();

        // Get variables
        $givenTitle = $request->input('title');
        $givenContent = $request->input('content');

        // Check if the user is banned
        $foundBan = ForumSectionBan::getBanInfo($request->user_id, $sectionID);

        if($foundBan !== null)
            (new JSONResult())->setError($foundBan['message'])->show();

        // Check if the user has already posted within the cooldown period
        if(ForumThread::testPostCooldown($request->user_id))
            (new JSONResult())->setError('You can only post a thread once every minute.')->show();

        // Create the thread
        $newThread = ForumThread::create([
            'section_id'    => $sectionID,
            'user_id'       => $request->user_id,
            'ip'            => $request->ip(),
            'title'         => $givenTitle,
            'content'       => $givenContent
        ]);

        (new JSONResult())->setData(['thread_id' => $newThread->id])->show();
    }
}
