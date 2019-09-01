<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ForumReplyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'        => $this->id,
            'posted_at' => $this->created_at->format('Y-m-d H:i:s'),
            'poster' => [
                'id'        => $this->user->id,
                'username'  => $this->user->username,
                'avatar'    => $this->user->getAvatarURL()
            ],
            'score'     => $this->likesDiffDislikesCount,
            'content'   => $this->content
        ];
    }
}
