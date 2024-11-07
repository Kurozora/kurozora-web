<?php

namespace App\Http\Resources;

use App\Models\Manga;
use App\Models\MediaStaff;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaStaffResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var MediaStaff $resource
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
        $routeName = match ($this->resource->model->getMorphClass()) {
            Manga::class => 'api.manga.staff',
            default => 'api.anime.staff',
        };
        $resource = [
            'id' => (string) $this->resource->id,
            'type' => 'staff',
            'href' => route($routeName, $this->resource->model, false),
            'attributes' => [
                'role' => $this->resource->staff_role->only(['name', 'description']),
            ]
        ];

        $relationships = [];
        $relationships = array_merge($relationships, $this->getPersonRelationship());

        $resource = array_merge($resource, ['relationships' => $relationships]);

        return $resource;
    }

    /**
     * Returns the person relationship for the resource.
     *
     * @return array
     */
    protected function getPersonRelationship(): array
    {
        return [
            'person' => [
                'href' => route('api.people.details', $this->resource, false),
                'data' => PersonResourceBasic::collection([$this->resource->person]),
            ]
        ];
    }
}
