<?php

namespace Laravel\Nova\Http\Controllers;

use Illuminate\Routing\Controller;
use Laravel\Nova\Http\Requests\ResourceIndexRequest;

class ResourceCountController extends Controller
{
    /**
     * Get the resource count for a given query.
     *
     * @param  \Laravel\Nova\Http\Requests\ResourceIndexRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(ResourceIndexRequest $request)
    {
        return response()->json(['count' => $request->toCount()]);
    }
}
