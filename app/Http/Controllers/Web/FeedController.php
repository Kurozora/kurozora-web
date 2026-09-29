<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FeedMessage;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    /**
     * Show the feed.
     *
     * @return Application|Factory|View
     */
    public function index(): Application|Factory|View
    {
        return view('feed.index');
    }

    /**
     * Show a feed message with its replies.
     *
     * @param Request     $request
     * @param FeedMessage $feedMessage
     *
     * @return Application|Factory|View
     */
    public function show(Request $request, FeedMessage $feedMessage): Application|Factory|View
    {
        $authUser = $request->user();

        $feedMessage->loadMissing(FeedMessage::lockupEagerLoads($authUser))
            ->loadCount(['replies', 'reShares']);

        if ($authUser !== null) {
            $feedMessage->loadExists([
                'simpleReShares as isReShared' => function ($query) use ($authUser) {
                    $query->where('user_id', '=', $authUser->id);
                },
            ]);
        }

        $replies = $feedMessage->replies()
            ->with(FeedMessage::lockupEagerLoads($authUser))
            ->withCount(['replies', 'reShares'])
            ->when($authUser, function ($query, $user) {
                $query->withExists(['simpleReShares as isReShared' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            })
            ->orderBy('created_at', 'desc')
            ->cursorPaginate(25, ['*'], 'fmc');

        return view('feed.show', [
            'feedMessage' => $feedMessage,
            'replies' => $replies,
            'title' => __(':author on :app: ":content" :url', [
                'author' => $feedMessage->user->username,
                'app' => config('app.name'),
                'content' => $feedMessage->content,
                'url' => url()->current(),
            ]),
        ]);
    }
}
