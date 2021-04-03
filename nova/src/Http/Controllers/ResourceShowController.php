<?php

namespace Laravel\Nova\Http\Controllers;

use Illuminate\Routing\Controller;
use Laravel\Nova\Http\Requests\ResourceDetailRequest;

class ResourceShowController extends Controller
{
    /**
     * Display the resource for administration.
     *
     * @param  \Laravel\Nova\Http\Requests\ResourceDetailRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function handle(ResourceDetailRequest $request)
    {
        $resource = $request->newResourceWith(tap($request->findModelQuery(), function ($query) use ($request) {
            $resource = $request->resource();
            $resource::detailQuery($request, $query);
        })->firstOrFail());

        $resource->authorizeToView($request);

        return response()->json([
            'title' => (string) $resource->title(),
            'panels' => $resource->availablePanelsForDetail($request, $resource),
            'resource' => $resource->serializeForDetail($request, $resource),
        ]);
    }
}
