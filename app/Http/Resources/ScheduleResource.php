<?php

namespace App\Http\Resources;

use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
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
            'date' => $this->resource['date'],
            $key => match ($this->resource['type']) {
                Anime::class => AnimeResource::collection($this->resource['models']),
                Game::class => GameResource::collection($this->resource['models']),
                Manga::class => LiteratureResource::collection($this->resource['models']),
            }
        ];
    }
}
