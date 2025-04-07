<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetPaginatedRequest;
use App\Http\Resources\SessionResource;
use App\Models\Session;
use Illuminate\Http\JsonResponse;

class SessionController extends Controller
{
    /**
     * Returns the current active sessions for a user
     *
     * @param GetPaginatedRequest $request
     * @return JsonResponse
     */
    public function index(GetPaginatedRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get the authenticated user
        $user = auth()->user();

        // Get paginated sessions except current session
        $sessions = $user->sessions()
            ->with(['session_attribute'])
            ->paginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $sessions->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => SessionResource::collection($sessions),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Displays token information
     *
     * @param Session $session
     * @return JsonResponse
     */
    public function details(Session $session): JsonResponse
    {
        return JSONResult::success([
            'data' => SessionResource::collection([$session])
        ]);
    }

    /**
     * Deletes a session
     *
     * @param Session $session
     * @return JsonResponse
     */
    public function delete(Session $session): JsonResponse
    {
        // Delete the session
        $session->delete();

        return JSONResult::success();
    }
}
