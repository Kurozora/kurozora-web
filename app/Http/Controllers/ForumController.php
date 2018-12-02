<?php

namespace App\Http\Controllers;

use App\ForumBan;
use App\ForumPost;
use App\ForumSection;
use App\Helpers\JSONResult;
use Illuminate\Http\Request;
use Validator;

class ForumController extends Controller
{
    /**
     * Returns the forum sections
     */
    public function getSections() {
        $rawSections = ForumSection::all();

        $sections = [];

        foreach($rawSections as $rawSection)
            $sections[] = $rawSection->formatForResponse();

        (new JSONResult())->setData(['sections' => $sections])->show();
    }

    /**
     * Returns the posts of a section
     */
    public function getPosts(Request $request) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'section_id'    => 'bail|required|numeric|exists:' . ForumSection::TABLE_NAME .',id',
            'order'         => 'bail|required|in:top,recent',
            'page'          => 'bail|numeric|min:0'
        ]);

        // Fetch the variables
        $givenSection = $request->input('section_id');
        $givenOrder = $request->input('order');
        $givenPage = $request->input('page');

        // Check validator
        if($validator->fails())
            (new JSONResult())->setError($validator->errors()->first())->show();

        // Create where clauses
        $whereClauses = [
            ['section_id', '=', $givenSection]
        ];

        // Add where clauses
        $rawPosts = ForumPost::where($whereClauses);

        // Add order
        if($givenOrder == 'top')
            $rawPosts->orderBy('score', 'DESC');
        else if($givenOrder == 'recent')
            $rawPosts->orderBy('created_at', 'DESC');

        // Add page/offset
        $resultsPerPage = 10;

        if($givenPage == null)
            $givenPage = 0;

        $rawPosts->offset($givenPage * $resultsPerPage);
        $rawPosts->limit($resultsPerPage);

        // Get the results
        $rawPosts = $rawPosts->get();

        $posts = [];

        foreach($rawPosts as $rawPost)
            $posts[] = $rawPost->formatForResponse();

        // Show posts in response
        (new JSONResult())->setData([
            'page'  => (int) $givenPage,
            'posts' => $posts
        ])->show();
    }

    public function postThread(Request $request) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'section_id'    => 'bail|required|numeric|exists:' . ForumSection::TABLE_NAME .',id',
            'title'         => 'bail|required|min:' . ForumPost::MIN_TITLE_LENGTH,
            'content'       => 'bail|required|min:' . ForumPost::MIN_CONTENT_LENGTH
        ]);

        // Check validator
        if($validator->fails())
            (new JSONResult())->setError($validator->errors()->first())->show();

        // Get variables
        $givenSection = (int) $request->input('section_id');
        $givenTitle = $request->input('title');
        $givenContent = $request->input('content');

        // Check if the user is banned
        if(ForumBan::where('section_id', $givenSection)->where('user_id', $request->user_id)->exists())
            (new JSONResult())->setError('You are banned from posting in this section.')->show();

        // Check if the user has already posted within the cooldown period
        if(ForumPost::testCooldown(true, $request->user_id))
            (new JSONResult())->setError('You can only post a thread once every ' . ForumPost::COOLDOWN_POST_THREAD . ' seconds.')->show();

        // Create the thread
        $newThread = ForumPost::create([
            'section_id'    => $givenSection,
            'user_id'       => $request->user_id,
            'ip'            => $request->ip(),
            'title'         => $givenTitle,
            'content'       => $givenContent
        ]);

        (new JSONResult())->setData(['created_post_id' => $newThread->id])->show();
    }
}
