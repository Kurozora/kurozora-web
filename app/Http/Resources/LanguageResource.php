<?php

namespace App\Http\Resources;

use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LanguageResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Language $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->resource->id,
            'type'          => 'languages',
            'href'          => route('api.languages.details', $this->resource, false),
            'attributes'    => [
                'name'          => $this->resource->name,
                'code'          => $this->resource->code,
            ]
        ];
    }
}
