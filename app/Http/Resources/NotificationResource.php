<?php

namespace App\Http\Resources;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Notification $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->resource->id,
            'type' => 'notifications',
            'href' => route('api.me.notifications.details', $this->resource, false),
            'attributes' => [
                'type' => class_basename($this->resource->type),
                'description' => $this->resource->description,
                'payload' => $this->resource->data,
                'isRead' => ($this->resource->read_at != null),
                'createdAt' => $this->resource->created_at->timestamp,
            ]
        ];
    }
}
