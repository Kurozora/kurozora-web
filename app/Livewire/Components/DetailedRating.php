<?php

namespace App\Livewire\Components;

use App\Enums\RatingStyle;
use App\Models\MediaRating;
use App\Models\MediaTypeCategory;
use App\Models\RatingCategoryScore;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Isolate;
use Livewire\Component;

#[Isolate]
class DetailedRating extends Component
{
    public ?string $modelID     = null;
    public ?string $modelType   = null;
    public float   $rating      = 0.0;
    public bool    $showModal   = false;
    public bool    $disabled    = false;
    public bool    $readyToLoad = false;

    /** @var array<string, float> */
    public array  $existingScores      = [];
    public string $existingDescription = '';

    protected function getListeners(): array
    {
        return $this->disabled ? [] : [
            $this->listenerKey() => 'handleRatingUpdate',
        ];
    }

    protected function listenerKey(): string
    {
        return 'detailed-rating-updated-' . $this->modelID . '-' . $this->modelType;
    }

    public function mount(
        ?string $modelId   = null,
        ?string $modelType = null,
        ?float  $rating    = null,
        bool    $disabled  = false
    ): void {
        $this->modelID   = $modelId;
        $this->modelType = $modelType;
        $this->rating    = $rating ?? MediaRating::MIN_RATING_VALUE;
        $this->disabled  = $disabled;
    }

    public function loadSection(): void
    {
        $this->readyToLoad = true;
        $this->loadExistingScores();
    }

    protected function loadExistingScores(): void
    {
        $user = auth()->user();

        if (!$user) {
            return;
        }

        $mediaRating = $user->mediaRatings()
            ->withoutGlobalScopes()
            ->where('model_id', $this->modelID)
            ->where('model_type', $this->modelType)
            ->first();

        if (!$mediaRating) {
            return;
        }

        $this->existingDescription = (string) ($mediaRating->description ?? '');

        foreach ($mediaRating->categoryScores as $score) {
            $this->existingScores[(string) $score->rating_category_id] = (float) $score->score;
        }
    }

    public function openModal(): void
    {
        if ($this->disabled) {
            return;
        }

        $this->showModal = true;

        if (!$this->readyToLoad) {
            $this->loadSection();
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    /**
     * @param array<string, float> $scores
     * @param string               $description
     */
    public function rate(array $scores, string $description = ''): null|RedirectResponse
    {
        $user = auth()->user();

        if (!$user) {
            return to_route('sign-in');
        }

        $categories  = MediaTypeCategory::categoriesFor($this->modelType);
        $totalWeight = 0.0;
        $weightedSum = 0.0;

        foreach ($categories as $category) {
            $key    = (string) $category->rating_category_id;
            $score  = isset($scores[$key]) ? (float) $scores[$key] : 5.0;
            $score  = max(MediaRating::MIN_RATING_VALUE, min(MediaRating::MAX_RATING_VALUE, $score));
            $weight = (float) ($category->ratingCategory->weight ?? 1.0);

            $weightedSum += $score * $weight;
            $totalWeight += $weight;
        }

        $this->rating = $totalWeight > 0
            ? round($weightedSum / $totalWeight, 1)
            : 5.0;

        $mediaRating = $user->mediaRatings()
            ->withoutGlobalScopes()
            ->updateOrCreate(
                ['model_id' => $this->modelID, 'model_type' => $this->modelType],
                [
                    'rating'       => $this->rating,
                    'rating_style' => RatingStyle::Detailed,
                    'description'  => trim($description) ?: null,
                ]
            );

        foreach ($scores as $categoryId => $score) {
            RatingCategoryScore::updateOrCreate(
                [
                    'rating_id'          => $mediaRating->id,
                    'rating_category_id' => (int) $categoryId,
                ],
                ['score' => max(MediaRating::MIN_RATING_VALUE, min(MediaRating::MAX_RATING_VALUE, (float) $score))]
            );
        }

        $this->existingScores      = collect($scores)
            ->mapWithKeys(fn ($v, $k) => [(string) $k => (float) $v])
            ->all();
        $this->existingDescription = trim($description);

        $this->closeModal();

        $this->dispatch($this->listenerKey(),
            id: $this->getID(), modelID: $this->modelID,
            modelType: $this->modelType, rating: $this->rating
        );
        $this->dispatch('star-rating-updated-' . $this->modelID . '-' . $this->modelType,
            id: $this->getID(), modelID: $this->modelID,
            modelType: $this->modelType, rating: $this->rating
        );

        return null;
    }

    public function removeRating(): null|RedirectResponse
    {
        $user = auth()->user();

        if (!$user) {
            return to_route('sign-in');
        }

        $user->mediaRatings()
            ->withoutGlobalScopes()
            ->where('model_id', $this->modelID)
            ->where('model_type', $this->modelType)
            ->forceDelete();

        $this->rating              = 0.0;
        $this->existingScores      = [];
        $this->existingDescription = '';

        $this->closeModal();

        $this->dispatch($this->listenerKey(),
            id: $this->getID(), modelID: $this->modelID,
            modelType: $this->modelType, rating: null
        );
        $this->dispatch('star-rating-updated-' . $this->modelID . '-' . $this->modelType,
            id: $this->getID(), modelID: $this->modelID,
            modelType: $this->modelType, rating: null
        );

        return null;
    }

    public function handleRatingUpdate($id, $modelID, $modelType, $rating): void
    {
        if ($this->getID() != $id && $modelID == $this->modelID && $modelType == $this->modelType) {
            $this->rating = (float) ($rating ?? 0.0);
        }
    }

    public function getCategoriesProperty(): Collection
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return MediaTypeCategory::categoriesFor($this->modelType);
    }

    public function render(): Application|Factory|View
    {
        return view('livewire.components.detailed-rating');
    }
}