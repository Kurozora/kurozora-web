<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Resources\SessionResource;
use App\Models\Session;
use Illuminate\Http\JsonResponse;

class SessionController extends Controller
{
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
