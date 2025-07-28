<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetPaginatedRequest;
use App\Http\Resources\UserResourceBasic;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UserBlockController extends Controller
{
    /**
     * Get the list of blocked users.
     *
     * @param GetPaginatedRequest $request
     *
     * @return JsonResponse
     */
    public function index(GetPaginatedRequest $request)
    {
        $data = $request->validated();

        $blockedUsers = $request->user()
            ->blockedModels()
            ->orderBy('username')
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $blockedUsers->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => UserResourceBasic::collection($blockedUsers),
            'next' => empty($nextPageURL) ? null : $nextPageURL,
        ]);
    }

    /**
     * Block the specified user.
     *
     * @param Request $request
     * @param User    $user
     *
     * @return JsonResponse
     */
    public function blockUser(Request $request, User $user)
    {
        $authUser = $request->user();

        if ($authUser->id === $user->id) {
            throw new BadRequestHttpException('You cannot block yourself.');
        }

        $isBlocked = $authUser->toggleBlock($user);

        return JSONResult::success([
            'data' => [
                'isBlocked' => $isBlocked,
            ]
        ]);
    }
}
