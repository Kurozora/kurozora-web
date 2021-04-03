<?php

namespace Laravel\Nova\Http\Controllers;

use Illuminate\Routing\Controller;
use Laravel\Nova\Http\Requests\NovaRequest;

class CreationPivotFieldController extends Controller
{
    /**
     * List the pivot fields for the given resource and relation.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function index(NovaRequest $request)
    {
        $resource = $request->newResourceWith(
            ($request->resourceId
                ? $request->findModelQuery()->first()
                : null) ?? $request->model()
        );

        return response()->json(
            $resource->creationPivotFields(
                $request,
                $request->relatedResource
            )->all()
        );
    }
}
