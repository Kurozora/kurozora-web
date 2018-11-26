<?php

namespace App\Http\Controllers;

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
}
