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
     * The challenger's game, included when joining a versus game.
     *
     * @var Game|null
     */
    protected ?Game $challenger;

    /**
     * Whether the word relationship is included.
     *
     * @var bool
     */
    protected bool $includesWord;

    /**
     * Create a new resource instance.
     *
     * @param Game      $resource
     * @param Game|null $challenger
     * @param bool      $includesWord
     */
    public function __construct(Game $resource, ?Game $challenger = null, bool $includesWord = true)
    {
        parent::__construct($resource);
        $this->challenger = $challenger;
        $this->includesWord = $includesWord;
    }

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray(Request $request): array
    {
        $this->resource->loadMissing(['word.subject.media', 'dailyPuzzle', 'guesses']);

        $attributes = [
            'mode' => $this->resource->mode?->value,
            'status' => $this->resource->status?->value,
            'guessCount' => (int) $this->resource->guess_count,
            'maxGuesses' => Game::MAX_GUESSES,
            'startedAt' => $this->resource->started_at?->timestamp,
            'finishedAt' => $this->resource->finished_at?->timestamp,
            'durationMs' => $this->resource->duration_ms,
            'versusSeed' => $this->resource->versus_seed,
            'guesses' => GuessResource::collection($this->resource->guesses),
        ];

        if ($this->resource->shouldRevealAnswer()) {
            $attributes['shareUrl'] = URL::signedRoute('api.kotodama.games.share', ['game' => $this->resource->id]);
        }

        $resource = [
            'id' => (string) $this->resource->id,
            'type' => 'kotodama-games',
            'href' => route('api.kotodama.games.details', $this->resource, false),
            'attributes' => $attributes,
        ];

        $relationships = [];

        if ($this->includesWord) {
            $relationships = array_merge($relationships, $this->getWordRelationship());
        }

        if ($this->resource->dailyPuzzle) {
            $relationships = array_merge($relationships, $this->getDailyPuzzleRelationship());
        }

        if ($this->challenger) {
            $relationships = array_merge($relationships, $this->getChallengerRelationship());
        }

        if (!empty($relationships)) {
            $resource = array_merge($resource, ['relationships' => $relationships]);
        }

        return $resource;
    }

    /**
     * Returns the word relationship for the resource.
     *
     * @return array
     */
    protected function getWordRelationship(): array
    {
        return [
            'words' => [
                'data' => [WordResource::make($this->resource->word, $this->resource)],
            ],
        ];
    }

    /**
     * Returns the daily puzzle relationship for the resource.
     *
     * @return array
     */
    protected function getDailyPuzzleRelationship(): array
    {
        return [
            'dailyPuzzles' => [
                'data' => [DailyPuzzleResource::make($this->resource->dailyPuzzle)],
            ],
        ];
    }

    /**
     * Returns the challenger relationship for the resource.
     *
     * The challenger's game leaves out the word so the answer stays hidden from the joiner.
     *
     * @return array
     */
    protected function getChallengerRelationship(): array
    {
        return [
            'challengers' => [
                'data' => [GameResource::make($this->challenger, null, false)],
            ],
        ];
    }
}
