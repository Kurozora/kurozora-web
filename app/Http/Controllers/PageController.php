<?php

namespace App\Http\Controllers;

use App\Anime;
use App\ForumThread;
use App\User;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Anime landing page
     *
     * @param $animeID
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function anime($animeID) {
        $anime = Anime::find($animeID);

        if(!$anime) abort(404);

        return view('website.anime-page', [
            'page' => [
                'title' => $anime->title,
                'type' => 'video.tv_show',
                'image' => $anime->cached_poster
            ],
            'animeData' => [
                'id'            => $anime->id,
                'title'         => $anime->title,
                'episode_count' => $anime->episode_count,
                'poster'        => $anime->cached_poster
            ]
        ]);
    }

    /**
     * User profile landing page
     *
     * @param $userID
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function userProfile($userID) {
        $user = User::find($userID);

        if(!$user) abort(404);

        $avatar = $user->getURLToAvatar();

        return view('website.user-profile-page', [
            'page' => [
                'title' => $user->username . ' on Kurozora',
                'type' => 'profile',
                'image' => $avatar
            ],
            'userData' => [
                'id'            => $user->id,
                'username'      => $user->username,
                'avatar'        => $avatar,
                'followers'     => $user->getFollowerCount()
            ]
        ]);
    }

    /**
     * Thread landing page
     *
     * @param $threadID
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function thread($threadID) {
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
