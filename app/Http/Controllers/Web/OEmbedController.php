<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetOEmbedRequest;
use App\Http\Resources\OEmbedResource;
use App\Models\Episode;
use App\Models\Song;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

class OEmbedController extends Controller
{
    /**
     * Return the oEmbed JSON.
     *
     * @param GetOEmbedRequest $request
     * @return JsonResponse
     */
    public function show(GetOEmbedRequest $request): JsonResponse
    {
        $data = $request->validated();

        $modelAttributes = str(parse_url($data['url'], PHP_URL_PATH))
            ->explode('/')
            ->filter()
            ->values();

        $model = match ($modelAttributes[0] ?? '') {
            'episodes' => Episode::firstWhere('id', '=', $modelAttributes[1] ?? ''),
            'songs' => Song::firstWhere('id', '=', $modelAttributes[1] ?? ''),
            default => throw new ModelNotFoundException()
        };

        return match ($data['format'] ?? 'json') {
            'xml' => $this->xmlResponse($model),
            default => $this->jsonResponse($model)
        };
    }

    /**
     * @param Model $model
     * @return JsonResponse
     */
    private function jsonResponse(Model $model): JsonResponse
    {
        return Response::json(OEmbedResource::make($model));
    }

    /**
     * @param Model $model
     * @return Application|Factory|View
     */
    private function xmlResponse(Model $model): Application|Factory|View
    {
        dd($model::class);
        return view('profile.settings', [
            'request' => $request,
            'user' => $request->user(),
        ]);
    }
}
