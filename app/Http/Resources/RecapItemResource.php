<?php

namespace App\Http\Resources;

use App\Models\Anime;
use App\Models\Character;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Manga;
use App\Models\MediaStaff;
use App\Models\Person;
use App\Models\Recap;
use App\Models\Studio;
use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecapItemResource extends JsonResource
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
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $resource = RecapItemResourceBasic::make($this->resource)->toArray($request);
        $relationships = array_merge($this->getTypeSpecificData($request), $this->getItemsRelationship());

        return array_merge($resource, ['relationships' => $relationships]);
    }

    /**
     * Returns the items relationship.
     *
     * @return array
     */
    protected function getItemsRelationship(): array
    {
        return [
            'items' => [
                'data' => RecapEntryResource::collection($this->resource->recapItems),
            ]
        ];
    }

    /**
     * Returns the relationships of the recap's type.
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
            Studio::class => [
                'studios' => [
                    'data' => StudioResourceIdentity::collection($this->resource
                        ->recapItems
                        ->pluck('model')
                        ->filter())
                ]
            ],
            Character::class => [
                'characters' => [
                    'data' => CharacterResourceIdentity::collection($this->resource
                        ->recapItems
                        ->pluck('model')
                        ->filter())
                ]
            ],
            Person::class, MediaStaff::class => [
                'people' => [
                    'data' => PersonResourceIdentity::collection($this->resource
                        ->recapItems
                        ->pluck('model')
                        ->filter())
                ]
            ],
            default => [
                'shows' => null
            ],
        };
    }
}
