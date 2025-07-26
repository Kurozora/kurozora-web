<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetOEmbedRequest;
use App\Http\Resources\OEmbedResource;
use App\Models\Anime;
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
     * @return Application|Factory|JsonResponse|View
     */
    public function show(GetOEmbedRequest $request): Application|Factory|JsonResponse|View
    {
        $data = $request->validated();

        $modelAttributes = str(parse_url($data['url'], PHP_URL_PATH))
            ->explode('/')
            ->filter()
            ->values();

        $model = match ($modelAttributes[0] ?? '') {
            'episodes' => Episode::withoutGlobalScopes()
                ->with([
                    'anime' => function ($query) {
                        $query->withoutGlobalScopes()
                            ->select([Anime::TABLE_NAME . '.id'])
                            ->with(['translation']);
                    },
                    'translation'
                ])
                ->firstWhere('id', '=', $modelAttributes[1] ?? ''),
            'songs' => Song::withoutGlobalScopes()
                ->with(['translation'])
                ->firstWhere('id', '=', $modelAttributes[1] ?? ''),
            default => throw new ModelNotFoundException()
        };

        return match ($data['format'] ?? 'json') {
            'xml' => $this->xmlResponse($request, $model),
            default => $this->jsonResponse($model)
        };
    }

    /**
     * Return the JSON response.
     *
     * @param Model $model
     *
     * @return JsonResponse
     */
    private function jsonResponse(Model $model): JsonResponse
    {
        return Response::json(OEmbedResource::make($model));
    }

    /**
     * Return the XML response.
     *
     * @param GetOEmbedRequest $request
     * @param Model $model
     *
     * @return Application|Factory|View
     */
    private function xmlResponse(GetOEmbedRequest $request, Model $model): Application|Factory|View
    {
        return view('xml.oembed', OEmbedResource::make($model)->toArray($request));
    }
}
