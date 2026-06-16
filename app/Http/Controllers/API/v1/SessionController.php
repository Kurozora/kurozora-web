<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetPaginatedRequest;
use App\Http\Requests\SignOutSessionsRequest;
use App\Http\Resources\SessionResource;
use App\Models\Session;
use Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

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
            ->with(['sessionAttribute'])
            ->cursorPaginate($data['limit'] ?? 25);

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

    /**
     * Deletes multiple or all sessions.
     *
     * @param SignOutSessionsRequest $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function deleteMultiple(SignOutSessionsRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = auth()->user();

        if (!Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => [__('This password does not match our records.')],
            ]);
        }

        $sessions = $user->sessions();

        if ($data['identities'] !== 'all') {
            $sessions->whereIn('id', array_filter(explode(',', $data['identities'])));
        }

        $sessions->get()
            ->each
            ->delete();

        return JSONResult::success();
    }
}
