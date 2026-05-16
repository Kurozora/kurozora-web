<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\BroadcastingAuthRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Broadcast;

class BroadcastingController extends Controller
{
    /**
     * Signs a private-channel authentication token for the authenticated user.
     *
     * @param BroadcastingAuthRequest $request
     *
     * @return JsonResponse
     */
    public function auth(BroadcastingAuthRequest $request): JsonResponse
    {
        $user = $request->user();
        $channelName = (string) $request->input('channel_name', '');

        if ($channelName === 'private-self') {
            $resolvedChannelName = 'private-users.' . $user->getKey();
            $request->merge(['channel_name' => $resolvedChannelName]);
        } else {
            $resolvedChannelName = $channelName;
        }

        $authResponse = Broadcast::auth($request);

        $authData = $authResponse instanceof JsonResponse
            ? $authResponse->getData(true)
            : (is_array($authResponse) ? $authResponse : []);

        return JSONResult::success([
            'data' => [
                'auth' => $authData['auth'] ?? '',
                'channelName' => $resolvedChannelName,
            ],
        ]);
    }
}
