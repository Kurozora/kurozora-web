<?php

namespace App\Http\Resources;

use App\Models\Game;
use App\Models\Genre;
use App\Models\Manga;
use App\Models\Recap;
use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecapItemResourceBasic extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Recap $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $resource = RecapItemResourceIdentity::make($this->resource)->toArray($request);
        $resource = array_merge($resource, [
            'attributes' => [
                'year' => $this->resource->year,
                'month' => $this->resource->month,
                'type' => match ($this->resource->type) {
                    Game::class => 'games',
                    Manga::class => 'literatures',
                    Genre::class => 'genres',
                    Theme::class => 'themes',
                    default => 'shows'
                },
                'totalSeriesCount' => $this->resource->total_series_count,
                'totalPartsCount' => $this->resource->total_parts_count,
                'totalPartsDuration' => $this->resource->total_parts_duration,
                'topPercentile' => $this->resource->top_percentile,
            ]
        ]);
        return $resource;
    }
}
