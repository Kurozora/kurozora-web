<?php

namespace Laravel\Nova\Http\Controllers;

use Illuminate\Routing\Controller;
use Laravel\Nova\Http\Requests\NovaRequest;

class UpdatePivotFieldController extends Controller
{
    /**
     * List the pivot fields for the given resource and relation.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function index(NovaRequest $request)
    {
        $resource = tap($request->findResourceOrFail(), function ($resource) use ($request) {
            abort_unless($resource->hasRelatableField($request, $request->viaRelationship), 404);
        });

        $model = $resource->model();

        $relation = $model->{$request->viaRelationship}();

        $accessor = $relation->getPivotAccessor();

        if ($request->viaPivotId) {
            tap($relation->getPivotClass(), function ($pivotClass) use ($relation, $request) {
                $relation->wherePivot((new $pivotClass())->getKeyName(), $request->viaPivotId);
            });
        }

        $model->setRelation(
            $accessor,
            $relation->withoutGlobalScopes()->findOrFail($request->relatedResourceId)->{$accessor}
        );

        return response()->json([
            'title' => $resource->title(),
            'fields' => $resource->updatePivotFields(
                $request,
                $request->relatedResource
            )->all(),
        ]);
    }
}
