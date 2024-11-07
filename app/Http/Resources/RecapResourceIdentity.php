<?php

namespace App\Http\Resources;

use App\Models\Recap;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecapResourceIdentity extends JsonResource
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
        return [
            'id' => (string) ($this->resource?->id ?? $this->resource),
            'type' => 'recaps',
            'href' => route('api.me.recap.view', [$this->resource?->year, $this->resource?->month], false),
        ];
    }
}
