<?php

namespace App\Http\Resources\Minigames\Kotodama;

use App\Http\Resources\AnimeResourceIdentity;
use App\Http\Resources\CharacterResourceIdentity;
use App\Http\Resources\GameResourceIdentity;
use App\Http\Resources\LiteratureResourceIdentity;
use App\Http\Resources\MediaResource;
use App\Http\Resources\PersonResourceIdentity;
use App\Http\Resources\SongResourceIdentity;
use App\Http\Resources\StudioResourceIdentity;
use App\Models\Anime;
use App\Models\Character;
use App\Models\Game as GameModel;
use App\Models\Manga;
use App\Models\Minigames\Kotodama\Game;
use App\Models\Minigames\Kotodama\Word;
use App\Models\Person;
use App\Models\Song;
use App\Models\Studio;
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
            $hintImage = $this->resource->getHintImage();
            $attributes['poster'] = $hintImage ? MediaResource::make($hintImage) : null;
            $attributes['subjectType'] = $this->resource->getSubjectKind();
        }

        if ($revealAnswer) {
            $attributes['answer'] = $this->resource->answer;
        }

        $resource = [
            'id' => (string) $this->resource->id,
            'type' => 'kotodama-words',
            'attributes' => $attributes,
        ];

        if ($revealAnswer && $this->resource->subject) {
            $resource = array_merge($resource, ['relationships' => $this->getSubjectRelationship()]);
        }

        return $resource;
    }

    /**
     * Returns the subject relationship for the resource.
     *
     * @return array
     */
    protected function getSubjectRelationship(): array
    {
        $identity = match (true) {
            $this->resource->subject instanceof Anime => AnimeResourceIdentity::make($this->resource->subject),
            $this->resource->subject instanceof Manga => LiteratureResourceIdentity::make($this->resource->subject),
            $this->resource->subject instanceof GameModel => GameResourceIdentity::make($this->resource->subject),
            $this->resource->subject instanceof Character => CharacterResourceIdentity::make($this->resource->subject),
            $this->resource->subject instanceof Person => PersonResourceIdentity::make($this->resource->subject),
            $this->resource->subject instanceof Studio => StudioResourceIdentity::make($this->resource->subject),
            $this->resource->subject instanceof Song => SongResourceIdentity::make($this->resource->subject),
            default => null,
        };

        if ($identity === null) {
            return [];
        }

        return [
            'subjects' => [
                'data' => [$identity],
            ],
        ];
    }
}
