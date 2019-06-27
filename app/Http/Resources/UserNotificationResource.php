<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserNotificationResource extends JsonResource
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
            'id'            => $this->id,
            'user_id'       => $this->user_id,
            'type'          => $this->getTypeString(),
            'read'          => (bool) $this->read,
            'data'          => $this->getData(),
            'string'        => $this->getString(),
            'creation_date' => (string) $this->created_at
        ];
    }
}
