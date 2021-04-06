<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\ForumThread;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Thread landing page
     *
     * @param $threadID
     * @return Application|Factory|View
     */
    public function thread($threadID)
    {
        $thread = ForumThread::find($threadID);

        if(!$thread) abort(404);

        return view('website.thread-page', [
            'page' => [
                'title' => $thread->title,
                'type' => 'website'
            ],
            'threadData' => [
                'id'            => $thread->id,
                'title'         => $thread->title,
                'date'          => $thread->created_at->diffForHumans()
            ]
        ]);
    }
}
