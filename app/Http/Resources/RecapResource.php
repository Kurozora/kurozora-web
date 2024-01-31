<?php

namespace App\Http\Resources;

use App\Models\Recap;
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

        // Merge relationships and return
        return $resource;
    }
}
