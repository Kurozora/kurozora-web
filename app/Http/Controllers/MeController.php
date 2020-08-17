<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Helpers\KuroAuthToken;
use App\Http\Resources\UserResource;
use App\Session;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    /**
     * Returns the profile details for the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        // Get authenticated session
        $sessionID = (int) $request->input('session_id');
        $session = Session::find($sessionID);

        return JSONResult::success([
            'data'      => [
                UserResource::make($session->user)->includingSession($session)
            ],
            'authToken' => KuroAuthToken::generate($session->user->id, $session->secret)
        ]);
    }
}
