<?php

namespace Laravel\Nova\Http\Controllers;

use Illuminate\Routing\Controller;
use Laravel\Nova\Http\Requests\ResourceIndexRequest;

class ResourceIndexController extends Controller
{
    /**
     * List the resources for administration.
     *
     * @param  \Laravel\Nova\Http\Requests\ResourceIndexRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(ResourceIndexRequest $request)
    {
        $resource = $request->resource();

        [$paginator, $total, $sortable] = $request->searchIndex();

        return response()->json([
            'label' => $resource::label(),
            'resources' => $paginator->getCollection()->mapInto($resource)->map->serializeForIndex($request),
            'prev_page_url' => $paginator->previousPageUrl(),
            'next_page_url' => $paginator->nextPageUrl(),
            'per_page' => $paginator->perPage(),
            'per_page_options' => $resource::perPageOptions(),
            'total' => $total,
            'softDeletes' => $resource::softDeletes(),
            'sortable' => $sortable ?? true,
        ]);
    }
}
