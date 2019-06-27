<?php

namespace App\Http\Controllers;

use App\ForumSection;
use App\ForumSectionBan;
use App\ForumThread;
use App\Helpers\JSONResult;
use App\Http\Requests\PostThread;
use App\Http\Resources\ForumSectionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ForumSectionController extends Controller
{
    /**
     * Generates an overview of forum sections.
     *
     * @return JsonResponse
     */
    public function overview() {
        $sections = ForumSection::all();

        return JSONResult::success([
            'sections' => ForumSectionResource::collection($sections)
        ]);
    }

    /**
     * Gets the information of a section
     *
     * @param ForumSection $section
     * @return JsonResponse
     */
    public function details(ForumSection $section) {
        return JSONResult::success([
            'section' => ForumSectionResource::make($section)
        ]);
    }

    /**
     * Returns the threads of a section
     *
     * @param Request $request
     * @param ForumSection $section
     * @return JsonResponse
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
            return JSONResult::error($validator->errors()->first());

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
        return JSONResult::success([
            'page'          => (int) $givenPage,
            'thread_pages'  => $section->getPageCount(),
            'threads'       => $displayThreads
        ]);
    }

    /**
     * Allows the user to submit a new thread
     *
     * @param PostThread $request
     * @param ForumSection $section
     * @return JsonResponse
     */
    public function postThread(PostThread $request, ForumSection $section) {
        $data = $request->validated();

        // Check if the user is banned
        $foundBan = ForumSectionBan::getBanInfo(Auth::id(), $section->id);

        if($foundBan !== null)
            return JSONResult::error($foundBan['message']);

        // Check if the user has already posted within the cooldown period
        if(ForumThread::testPostCooldown(Auth::id()))
            return JSONResult::error('You can only post a thread once every minute.');

        // Create the thread
        $newThread = ForumThread::create([
            'section_id'    => $section->id,
            'user_id'       => Auth::id(),
            'ip'            => $request->ip(),
            'title'         => $data['title'],
            'content'       => $data['content']
        ]);

        return JSONResult::success([
            'thread_id' => $newThread->id
        ]);
    }
}
