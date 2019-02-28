<?php

namespace App\Http\Controllers;

use App\ForumSection;
use App\ForumSectionBan;
use App\ForumThread;
use App\Helpers\JSONResult;
use App\Http\Requests\PostThread;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
     * @param ForumSection $section
     */
    public function details(ForumSection $section) {
        (new JSONResult())->setData([
            'section' => $section->formatForDetailsResponse()
        ])->show();
    }

    /**
     * Returns the threads of a section
     *
     * @param Request $request
     * @param ForumSection $section
     */
    public function threads(Request $request, ForumSection $section) {
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

        // Get the threads
        $threads = $section->threads();

        if($givenOrder == 'recent')
            $threads = $threads->orderBy('created_at', 'DESC');
        else if($givenOrder == 'top')
            $threads = $threads->orderByLikesCount();

        $threads = $threads->paginate(ForumSection::THREADS_PER_PAGE);

        // Format the threads
        $displayThreads = [];

        foreach($threads as $thread)
            $displayThreads[] = [
                'id'                => $thread->id,
                'title'             => $thread->title,
                'content_teaser'    =>
                    substr(strip_tags($thread->content), 0, 100) .
                    ((strlen($thread->content) > 100) ? '...' : '')
                ,
                'locked'            => (bool) $thread->locked,
                'poster_user_id'    => $thread->user->id,
                'poster_username'   => $thread->user->username,
                'creation_date'     => $thread->created_at->format('Y-m-d H:i:s'),
                'reply_count'       => $thread->replies->count(),
                'score'             => $thread->likesDiffDislikesCount
            ];

        // Show threads in response
        (new JSONResult())->setData([
            'page'          => (int) $givenPage,
            'thread_pages'  => $section->getPageCount(),
            'threads'       => $displayThreads
        ])->show();
    }

    /**
     * Allows the user to submit a new thread
     *
     * @param PostThread $request
     * @param ForumSection $section
     */
    public function postThread(PostThread $request, ForumSection $section) {
        $data = $request->validated();

        // Check if the user is banned
        $foundBan = ForumSectionBan::getBanInfo(Auth::id(), $section->id);

        if($foundBan !== null)
            (new JSONResult())->setError($foundBan['message'])->show();

        // Check if the user has already posted within the cooldown period
        if(ForumThread::testPostCooldown(Auth::id()))
            (new JSONResult())->setError('You can only post a thread once every minute.')->show();

        // Create the thread
        $newThread = ForumThread::create([
            'section_id'    => $section->id,
            'user_id'       => Auth::id(),
            'ip'            => $request->ip(),
            'title'         => $data['title'],
            'content'       => $data['content']
        ]);

        (new JSONResult())->setData(['thread_id' => $newThread->id])->show();
    }
}
