<?php

namespace App\Http\Resources;

use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlatformResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Session $session */
        $session = $this->resource;

        return [
            'type'          => 'platforms',
            'attributes'    => [
                'description'       => $session->humanReadablePlatform(),
                'systemName'        => $session->platform,
                'systemVersion'     => $session->platform_version,
                'deviceVendor'      => $session->device_vendor,
                'deviceModel'       => $session->device_model
            ]
        ];
    }
}
