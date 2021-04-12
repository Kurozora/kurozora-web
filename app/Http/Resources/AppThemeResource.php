<?php

namespace App\Http\Resources;

use App\Models\AppTheme;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppThemeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var AppTheme $theme */
        $theme = $this->resource;

        return [
            'id'            => $theme->id,
            'type'          => 'themes',
            'href'          => route('api.themes.details', $theme, false),
            'attributes'        => [
                'name'          => $theme->name,
                'screenshot'    => $theme->screenshot,
                'downloadLink'  => route('api.themes.download', ['theme' => $theme->id])
            ]
        ];
    }
}
