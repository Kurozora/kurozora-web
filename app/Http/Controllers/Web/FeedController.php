<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FeedMessage;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    /**
     * The list of valid activity tabs.
     */
    private const array ACTIVITY_TABS = ['quotes', 'reshares'];

    /**
     * The list of valid activity sorts.
     */
    private const array ACTIVITY_SORTS = ['top', 'recent'];

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

    /**
     * Show the quotes and re-shares of a feed message.
     *
     * @param Request     $request
     * @param FeedMessage $feedMessage
     *
     * @return Application|Factory|View
     */
    public function activity(Request $request, FeedMessage $feedMessage): Application|Factory|View
    {
        $authUser = $request->user();
        $tab = $request->string('tab')->value();
        $sort = $request->string('sort')->value();

        if (!in_array($tab, self::ACTIVITY_TABS, true)) {
            $tab = 'quotes';
        }

        if (!in_array($sort, self::ACTIVITY_SORTS, true)) {
            $sort = 'recent';
        }

        if ($authUser !== null) {
            $feedMessage->loadExists([
                'simpleReShares as isReShared' => function ($query) use ($authUser) {
                    $query->where('user_id', '=', $authUser->id);
                },
            ]);
        }

        if ($tab === 'reshares') {
            $query = $feedMessage->simpleReShares()
                ->with([
                    'user' => function (BelongsTo $belongsTo) use ($authUser) {
                        $belongsTo->with(['media'])
                            ->withCount(['followers'])
                            ->when($authUser, function ($query, $user) {
                                $query->withExists(['followers as isFollowed' => function ($query) use ($user) {
                                    $query->where('user_id', '=', $user->id);
                                }]);
                            });
                    },
                ]);
        } else {
            $query = $feedMessage->quoteReShares()
                ->with(FeedMessage::lockupEagerLoads($authUser))
                ->withCount(['replies', 'reShares'])
                ->when($authUser, function ($query, $user) {
                    $query->withExists(['simpleReShares as isReShared' => function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    }]);
                });
        }

        match ($sort) {
            'top'   => $query->orderByDesc('ranking_score')->orderByDesc('created_at'),
            default => $query->orderByDesc('created_at'),
        };

        return view('feed.activity', [
            'feedMessage' => $feedMessage,
            'tab' => $tab,
            'sort' => $sort,
            'feedMessages' => $query->paginate(25)->withQueryString(),
        ]);
    }
}
