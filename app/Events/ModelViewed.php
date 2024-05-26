<?php

namespace App\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ModelViewed
{
    use Dispatchable, SerializesModels;

    /**
     * @var Model $model
     */
    public Model $model;

    /**
     * @var ?string $ip
     */
    public ?string $ip;

    /**
     * Create a new event instance.
     *
     * @param Model       $model
     * @param null|string $ip
     */
    public function __construct(Model $model, ?string $ip)
    {
        $this->model = $model;
        $this->ip = $ip;
    }
}
