<?php

namespace App\Traits\Controller;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

trait WithStateVersionETag
{
    /**
     * Returns a 304 `JsonResponse` when the client's `If-None-Match` already matches the state-version ETag.
     *
     * @param Request $request
     * @param User    $user
     * @param array   $fingerprint
     * @return JsonResponse|null
     */
    protected function returnIfNotModified(Request $request, User $user, array $fingerprint): ?JsonResponse
    {
        $etag = $this->stateVersionETag($user, $fingerprint);

        if ($request->header('If-None-Match') === $etag) {
            return response()->json(null, Response::HTTP_NOT_MODIFIED, $this->stateVersionHeaders($etag, $user));
        }

        return null;
    }

    /**
     * Returns the headers a state-version ETag-aware read should attach to its response.
     *
     * @param string $etag
     * @param User   $user
     * @return array
     */
    protected function stateVersionHeaders(string $etag, User $user): array
    {
        return [
            'ETag' => $etag,
            'X-State-Version' => (string) $user->state_version,
            'Cache-Control' => 'private, no-cache',
        ];
    }

    /**
     * Returns the state-version ETag for the given user and request fingerprint.
     *
     * @param User  $user
     * @param array $fingerprint
     * @return string
     */
    protected function stateVersionETag(User $user, array $fingerprint): string
    {
        ksort($fingerprint);

        return '"' . sha1($user->id . ':' . $user->state_version . ':' . md5(json_encode($fingerprint))) . '"';
    }
}
