<?php

namespace App\Http\Resources;

use App\Models\Anime;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Manga;
use App\Models\Recap;
use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecapResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Recap $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $resource = RecapResourceBasic::make($this->resource)->toArray($request);

        $relationships = [];
        // Add specific data per type
        $relationships = array_merge($relationships, $this->getTypeSpecificData($request));

        // Merge relationships and return
        return array_merge($resource, ['relationships' => $relationships]);
    }

    /**
     * Returns specific data that should be added
     * depending on the type of the recap.
     *
     * @param Request $request
     *
     * @return array
     */
    private function getTypeSpecificData(Request $request): array
    {
        return match ($this->resource->type) {
            Genre::class => [
                'genres' => [
                    'data' => GenreResourceIdentity::collection($this->resource
                        ->recapItems
                        ->pluck('model'))
                ]
            ],
            Theme::class => [
                'themes' => [
                    'data' => ThemeResourceIdentity::collection($this->resource
                        ->recapItems
                        ->pluck('model'))
                ]
            ],
            Anime::class => [
                'shows' => [
                    'data' => AnimeResourceIdentity::collection($this->resource
                        ->recapItems
                        ->pluck('model'))
                ]
            ],
            Manga::class => [
                'literatures' => [
                    'data' => LiteratureResourceIdentity::collection($this->resource
                        ->recapItems
                        ->pluck('model'))
                ]
            ],
            Game::class => [
                'games' => [
                    'data' => GameResourceIdentity::collection($this->resource
                        ->recapItems
                        ->pluck('model'))
                ]
            ],
            default => [
                'shows' => null
            ],
        };
    }
}
