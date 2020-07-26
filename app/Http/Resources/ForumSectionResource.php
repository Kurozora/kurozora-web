<?php

namespace App\Http\Resources;

use App\ForumSection;
use Illuminate\Http\Resources\Json\JsonResource;

class ForumSectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var ForumSection $forumSection */
        $forumSection = $this->resource;

        return [
            'id'            => $forumSection->id,
            'type'          => 'sections',
            'href'          => route('forum-sections.details', $forumSection, false),
            'attributes'    => [
                'name'          => $forumSection->name,
                'locked'        => (bool) $forumSection->locked
            ]
        ];
    }
}
