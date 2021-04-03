<?php

namespace App\Http\Resources;

use App\Models\ForumSection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ForumSectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var ForumSection $forumSection */
        $forumSection = $this->resource;

        return [
            'id'            => $forumSection->id,
            'type'          => 'sections',
            'href'          => route('api.forum-sections.details', $forumSection, false),
            'attributes'    => [
                'name'      => $forumSection->name,
                'isLocked'  => (bool) $forumSection->locked
            ]
        ];
    }
}
