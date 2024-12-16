<?php

namespace App\Http\Resources;

use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $key = match ($this->resource['type']) {
            Anime::class => 'shows',
            Game::class => 'games',
            Manga::class => 'literatures',
        };

        return [
            'type' => $key . '-schedule',
            'attributes' => [
                'date' => Carbon::createFromFormat('Y-m-d', $this->resource['date'])->startOfDay()->timestamp,
            ],
            'relationships' => [
                $key => [
                    'data' => match ($this->resource['type']) {
                        Anime::class => AnimeResource::collection($this->resource['models']),
                        Game::class => GameResource::collection($this->resource['models']),
                        Manga::class => LiteratureResource::collection($this->resource['models']),
                    }
                ]
            ]
        ];
    }
}
