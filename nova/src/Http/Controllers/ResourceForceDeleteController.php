<?php

namespace Laravel\Nova\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Actions\Actionable;
use Laravel\Nova\Http\Requests\ForceDeleteResourceRequest;
use Laravel\Nova\Nova;

class ResourceForceDeleteController extends Controller
{
    use DeletesFields;

    /**
     * Force delete the given resource(s).
     *
     * @param  \Laravel\Nova\Http\Requests\ForceDeleteResourceRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function handle(ForceDeleteResourceRequest $request)
    {
        $request->chunks(150, function ($models) use ($request) {
            $models->each(function ($model) use ($request) {
                $this->forceDeleteFields($request, $model);

                if (in_array(Actionable::class, class_uses_recursive($model))) {
                    $model->actions()->delete();
                }

                $model->forceDelete();

                tap(Nova::actionEvent(), function ($actionEvent) use ($model, $request) {
                    DB::connection($actionEvent->getConnectionName())->table('action_events')->insert(
                        $actionEvent->forResourceDelete($request->user(), collect([$model]))
                            ->map->getAttributes()->all()
                    );
                });
            });
        });

        if ($request->isForSingleResource() && ! is_null($redirect = $request->resource()::redirectAfterDelete($request))) {
            return response()->json([
                'redirect' => $redirect,
            ]);
        }
    }
}
