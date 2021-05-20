<?php

namespace Laravel\Nova\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Http\Requests\CreateResourceRequest;
use Laravel\Nova\Nova;

class ResourceStoreController extends Controller
{
    /**
     * Create a new resource.
     *
     * @param  \Laravel\Nova\Http\Requests\CreateResourceRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(CreateResourceRequest $request)
    {
        $resource = $request->resource();

        $resource::authorizeToCreate($request);

        $resource::validateForCreation($request);

        $model = DB::transaction(function () use ($request, $resource) {
            [$model, $callbacks] = $resource::fill(
                $request, $resource::newModel()
            );

            $this->storeResource($request, $model);

            Nova::actionEvent()->forResourceCreate($request->user(), $model)->save();

            collect($callbacks)->each->__invoke();

            return $model;
        });

        return response()->json([
            'id' => $model->getKey(),
            'resource' => $model->attributesToArray(),
            'redirect' => $resource::redirectAfterCreate($request, $request->newResourceWith($model)),
        ], 201);
    }

    /**
     * Save the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\CreateResourceRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    protected function storeResource(CreateResourceRequest $request, Model $model)
    {
        if (! $request->viaRelationship()) {
            $model->save();

            return;
        }

        $relation = tap($request->findParentResourceOrFail(), function ($resource) use ($request) {
            abort_unless($resource->hasRelatableField($request, $request->viaRelationship), 404);
        })->model()->{$request->viaRelationship}();

        if ($relation instanceof HasManyThrough) {
            $model->save();

            return;
        }

        $relation->save($model);
    }
}
