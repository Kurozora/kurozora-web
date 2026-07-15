<?php

namespace App\Traits\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

trait WithCatalogCacheHeaders
{
    /**
     * Returns a 304 `JsonResponse` when the client's `If-None-Match` matches the catalog ETag.
     *
     * @param Request $request
     * @param array   $fingerprint
     * @return JsonResponse|null
     */
    protected function returnIfNotModifiedCatalog(Request $request, array $fingerprint): ?JsonResponse
    {
        if (!$this->wantsStripped($request)) {
            return null;
        }

        $etag = $this->catalogETag($fingerprint);

        if ($request->header('If-None-Match') === $etag) {
            return response()->json(null, Response::HTTP_NOT_MODIFIED, $this->catalogCacheHeaders($request, $fingerprint));
        }

        return null;
    }

    /**
     * Returns the cache headers a catalog read should attach to its response.
     *
     * @param Request $request
     * @param array   $fingerprint
     * @return array
     */
    protected function catalogCacheHeaders(Request $request, array $fingerprint): array
    {
        if (!$this->wantsStripped($request)) {
            return [];
        }

        return [
            'ETag' => $this->catalogETag($fingerprint),
            'Cache-Control' => 'public, max-age=300, stale-while-revalidate=60',
            'Vary' => 'Accept-Language, Accept-Encoding',
        ];
    }

    /**
     * Returns the catalog ETag for the given URL-derived fingerprint.
     *
     * @param array $fingerprint
     * @return string
     */
    protected function catalogETag(array $fingerprint): string
    {
        ksort($fingerprint);

        return '"' . sha1(json_encode($fingerprint)) . '"';
    }

    /**
     * Indicates whether the request opted into the stripped (cacheable) variant via `?embedded=0`.
     *
     * @param Request $request
     * @return bool
     */
    protected function wantsStripped(Request $request): bool
    {
        return $request->boolean('embedded', true) === false;
    }
}
