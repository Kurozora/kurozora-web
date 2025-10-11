<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\LinkPreviewService;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;

class LinkPreviewController extends Controller
{
    /**
     * @throws InvalidEnumKeyException
     * @throws ConnectionException
     */
    public function show(Request $request, LinkPreviewService $service)
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        $preview = $service->resolve($request->input('url'));

        return response()->json([
            'url' => $preview->url,
            'type' => $preview->type,
            'title' => $preview->title,
            'description' => $preview->description,
            'media_url' => $preview->media_url,
            'embed_html' => $preview->embed_html,
            'provider' => $preview->provider,
            'fetched_at' => $preview->fetched_at,
        ]);
    }
}
