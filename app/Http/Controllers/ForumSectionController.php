<?php

namespace App\Http\Controllers;

use App\Enums\ForumOrderType;
use App\Models\ForumSection;
use App\Models\ForumSectionBan;
use App\Models\ForumThread;
use App\Helpers\JSONResult;
use App\Http\Requests\GetThreadsRequest;
use App\Http\Requests\PostThreadRequest;
use App\Http\Resources\ForumSectionResource;
use App\Http\Resources\ForumThreadResource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class ForumSectionController extends Controller
{
    /**
     * Generates an overview of forum sections.
     *
     * @return JsonResponse
     */
    public function overview(): JsonResponse
    {
        $sections = ForumSection::all();

        return JSONResult::success([
            'data' => ForumSectionResource::collection($sections)
        ]);
    }

    /**
     * Gets the information of a section
     *
     * @param ForumSection $section
     * @return JsonResponse
     */
    public function details(ForumSection $section): JsonResponse
    {
        return JSONResult::success([
            'data' => ForumSectionResource::collection([$section])
        ]);
    }

    /**
     * Returns the threads of a section.
     *
     * @param GetThreadsRequest $request
     * @param ForumSection $section
     * @return JsonResponse
     */
    public function threads(GetThreadsRequest $request, ForumSection $section): JsonResponse
    {
        // Fetch the variables
        $givenOrder = $request->input('order');

        // Get the threads
        /** @var ForumThread $threads */
        $threads = $section->forum_threads();
        $forumOrder = ForumOrderType::fromValue($givenOrder);

        switch ($forumOrder) {
            case ForumOrderType::Best:
                $threads->joinReactionTotal()
                        ->orderBy('reaction_total_weight', 'desc');
                break;
            case ForumOrderType::Top:
                $threads->joinReactionCounterOfType('Like')
                        ->orderBy('reaction_like_count', 'desc');
                break;
            case ForumOrderType::New:
                $threads->orderBy('created_at', 'desc');
                break;
            case ForumOrderType::Old:
                $threads->orderBy('created_at', 'asc');
                break;
            case ForumOrderType::Poor:
                $threads->joinReactionCounterOfType('Like')
                        ->orderBy('reaction_like_count', 'asc');
                break;
            case ForumOrderType::Controversial:
                $threads->joinReactionTotal()
                        ->orderBy('reaction_total_weight', 'asc');
                break;
        }

        $threads = $threads->paginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $threads->nextPageUrl());

        // Show threads in response
        return JSONResult::success([
            'data' => ForumThreadResource::collection($threads),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Allows the user to submit a new thread
     *
     * @param PostThreadRequest $request
     * @param ForumSection $section
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws TooManyRequestsHttpException
     */
    public function postThread(PostThreadRequest $request, ForumSection $section): JsonResponse
    {
        $data = $request->validated();

        // Check if the user is banned
        $foundBan = ForumSectionBan::getBanInfo(Auth::id(), $section->id);

        if ($foundBan !== null)
            throw new AuthorizationException($foundBan['message']);

        // Check if the user has already posted within the cooldown period
        if (ForumThread::testPostCooldown(Auth::id()))
            throw new TooManyRequestsHttpException(60, 'You can only post a thread once every minute.');

        // Create the thread
        $newThread = ForumThread::create([
            'section_id'    => $section->id,
            'user_id'       => Auth::id(),
            'ip_address'    => $request->ip(),
            'title'         => $data['title'],
            'content'       => $data['content']
        ]);

        return JSONResult::success([
            'data' => ForumThreadResource::collection([$newThread])
        ]);
    }
}
