<?php

namespace App\Http\Resources;

use App\AppTheme;
use Illuminate\Http\Resources\Json\JsonResource;

class AppThemeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var AppTheme $theme */
        $theme = $this->resource;

        return [
            'id'            => $theme->id,
            'type'          => 'themes',
//            'href'          => route('themes.details', $theme, false),
            'attributes'        => [
                'name'          => $theme->name,
                'screenshot'    => $theme->getFirstMediaFullUrl('screenshot'),
                'download_link' => route('themes.download', ['theme' => $theme->id])
            ]
        ];
    }
}
