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
     * Anime landing page
     *
     * @param Anime $anime
     * @return Application|Factory|View
     */
    public function anime(Anime $anime)
    {
        $poster = $anime->poster();
        $posterURL = null;
        if($poster)
            $posterURL = $poster->url;

        return view('website.anime-page', [
            'page' => [
                'title' => $anime->title,
                'type' => 'video.tv_show',
                'image' => $posterURL
            ],
            'animeData' => [
                'id'            => $anime->id,
                'title'         => $anime->title,
                'episode_count' => $anime->episode_count,
                'poster'        => $posterURL
            ]
        ]);
    }

    /**
     * User profile landing page
     *
     * @param $userID
     * @return Application|Factory|View
     */
    public function userProfile($userID)
    {
        $user = User::find($userID);

        if(!$user) abort(404);

        $avatar = $user->getFirstMediaUrl('avatar');

        if (!$avatar)
            $avatar = null;

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
