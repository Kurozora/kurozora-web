<?php

namespace App\Http\Resources\Minigames\Kotodama;

use App\Models\Minigames\Kotodama\Game;
use App\Models\Minigames\Kotodama\Word;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WordResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Word $resource
     */
    public $resource;

    /**
     * The game this word is tied to.
     *
     * @var Game|null
     */
    protected ?Game $game;

    /**
     * Create a new resource instance.
     *
     * @param Word      $resource
     * @param Game|null $game
     */
    public function __construct(Word $resource, ?Game $game = null)
    {
        parent::__construct($resource);
        $this->game = $game;
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
        $revealAnswer = $this->game?->shouldRevealAnswer() ?? false;
        $guessCount = (int) ($this->game?->guess_count ?? 0);

        $revealHint = $revealAnswer || $guessCount >= Game::HINT_REVEAL_THRESHOLD;
        $revealSubjectPoster = $revealAnswer || $guessCount >= Game::SUBJECT_REVEAL_THRESHOLD;

        $attributes = [
            'length' => Word::LENGTH,
            'difficulty' => $this->resource->difficulty?->value,
        ];

        if ($revealHint) {
            $attributes['hint'] = $this->resource->getHint();
        }

        if ($revealSubjectPoster) {
            $attributes['subject'] = $this->buildSubjectPayload($revealAnswer);
        }

        if ($revealAnswer) {
            $attributes['answer'] = $this->resource->answer;
        }

        return [
            'id' => (string) $this->resource->id,
            'type' => 'kotodama-words',
            'attributes' => $attributes,
        ];
    }

    /**
     * The linked subject's payload.
     *
     * @param bool $includeIdentifiers
     *
     * @return array|null
     */
    protected function buildSubjectPayload(bool $includeIdentifiers): ?array
    {
        $subject = $this->resource->subject;

        if (!$subject) {
            return null;
        }

        $payload = [
            'posterUrl' => $this->resource->getHintImageUrl(),
        ];

        if ($includeIdentifiers) {
            $payload['type'] = $this->resource->subject_type;
            $payload['id'] = (string) $this->resource->subject_id;
            $payload['title'] = $this->resource->getSubjectTitle();
        }

        return $payload;
    }
}
