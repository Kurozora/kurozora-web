<?php

namespace App\Http\Resources\Minigames\Kotodama;

use App\Models\Minigames\Kotodama\Game;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

class GameResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Game $resource
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
        $this->resource->loadMissing(['word.subject', 'dailyPuzzle', 'guesses']);

        $attributes = [
            'mode' => $this->resource->mode?->value,
            'status' => $this->resource->status?->value,
            'guessCount' => (int) $this->resource->guess_count,
            'maxGuesses' => Game::MAX_GUESSES,
            'startedAt' => $this->resource->started_at?->timestamp,
            'finishedAt' => $this->resource->finished_at?->timestamp,
            'durationMs' => $this->resource->duration_ms,
            'versusSeed' => $this->resource->versus_seed,
            'word' => WordResource::make($this->resource->word, $this->resource),
            'guesses' => GuessResource::collection($this->resource->guesses),
        ];

        if ($this->resource->dailyPuzzle) {
            $attributes['dailyPuzzle'] = DailyPuzzleResource::make($this->resource->dailyPuzzle, $this->resource);
        }

        if ($this->resource->shouldRevealAnswer()) {
            $attributes['shareUrl'] = URL::signedRoute('api.kotodama.games.share', ['game' => $this->resource->id]);
        }

        return [
            'id' => (string) $this->resource->id,
            'type' => 'kotodama-games',
            'attributes' => $attributes,
        ];
    }
}
