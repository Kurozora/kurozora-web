<?php

namespace App\Http\Controllers;

use App\Actor;
use App\Helpers\JSONResult;
use App\Http\Resources\ActorResource;
use Illuminate\Http\JsonResponse;

class ActorController extends Controller
{
    /**
     * Generate an overview of actors.
     *
     * @return JsonResponse
     */
    public function overview()
    {
        // Get all actors and format them
        $allActors = Actor::get()->map(function($actor) {
            return ActorResource::make($actor);
        });

        // Show actors in response
        return JSONResult::success(['data' => $allActors]);
    }

    /**
     * Shows actor details.
     *
     * @param Actor $actor
     *
     * @return JsonResponse
     */
    public function details(Actor $actor)
    {
        // Show actor details
        return JSONResult::success([
            'data' => ActorResource::collection([$actor])
        ]);
    }
}
