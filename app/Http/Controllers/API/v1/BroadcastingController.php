<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\BroadcastingAuthRequest;
use App\Services\Presence\SocketSourceMarker;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Broadcast;

class BroadcastingController extends Controller
{
    /**
     * Signs a private-channel authentication token for the authenticated user.
     *
     * @param BroadcastingAuthRequest $request
     * @param SocketSourceMarker      $sourceMarker
     *
     * @return JsonResponse
     */
    public function auth(BroadcastingAuthRequest $request, SocketSourceMarker $sourceMarker): JsonResponse
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

        $sourceMarker->markApi((string) $request->input('socket_id', ''));

        return JSONResult::success([
            'data' => [
                'auth' => $authData['auth'] ?? '',
                'channelName' => $resolvedChannelName,
            ],
        ]);
    }
}
