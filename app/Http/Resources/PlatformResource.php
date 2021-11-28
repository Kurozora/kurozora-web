<?php

namespace App\Http\Resources;

use App\Models\SessionAttribute;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlatformResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var SessionAttribute
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
            'type'          => 'platforms',
            'attributes'    => [
                'description'       => $this->resource->humanReadablePlatform(),
                'systemName'        => $this->resource->platform,
                'systemVersion'     => $this->resource->platform_version,
                'deviceVendor'      => $this->resource->device_vendor,
                'deviceModel'       => $this->resource->device_model
            ]
        ];
    }
}
