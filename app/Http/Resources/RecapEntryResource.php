<?php

namespace App\Http\Resources;

use App\Models\Anime;
use App\Models\Character;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Manga;
use App\Models\Person;
use App\Models\RecapItem;
use App\Models\Studio;
use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecapEntryResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var RecapItem $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray(Request $request): array
    {
        $relationships = $this->getModelRelationship();

        return [
            'id' => (string) $this->resource->id,
            'type' => 'recap-entries',
            'attributes' => [
                'position' => $this->resource->position,
                'partsCount' => $this->resource->parts_count,
                'partsDuration' => round_to_nearest_quarter($this->resource->parts_duration / 60),
                'role' => $this->resource->role?->name,
            ],
            'relationships' => $this->when(!empty($relationships), $relationships),
        ];
    }

    /**
     * Returns the relationship of the entry's model.
     *
     * @return array
     */
    protected function getModelRelationship(): array
    {
        $model = $this->resource->model;

        if ($model === null) {
            return [];
        }

        return match ($this->resource->model_type) {
            Anime::class => ['shows' => ['data' => [AnimeResourceIdentity::make($model)]]],
            Manga::class => ['literatures' => ['data' => [LiteratureResourceIdentity::make($model)]]],
            Game::class => ['games' => ['data' => [GameResourceIdentity::make($model)]]],
            Genre::class => ['genres' => ['data' => [GenreResourceIdentity::make($model)]]],
            Theme::class => ['themes' => ['data' => [ThemeResourceIdentity::make($model)]]],
            Studio::class => ['studios' => ['data' => [StudioResourceIdentity::make($model)]]],
            Character::class => ['characters' => ['data' => [CharacterResourceIdentity::make($model)]]],
            Person::class => ['people' => ['data' => [PersonResourceIdentity::make($model)]]],
            default => [],
        };
    }
}
