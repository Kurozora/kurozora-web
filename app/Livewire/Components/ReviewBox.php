<?php

namespace App\Livewire\Components;

use App\Enums\RatingStyle;
use App\Models\MediaRating;
use App\Models\RatingCategory;
use App\Models\RatingCategoryScore;
use App\Support\UserLibraryTouch;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Livewire\Attributes\Isolate;
use Livewire\Component;

#[Isolate]
class ReviewBox extends Component
{
    /**
     * The id of the review box component.
     *
     * @var string $reviewBoxID
     */
    public string $reviewBoxID;

    /**
     * The object containing the model id.
     *
     * @var string $modelID
     */
    public string $modelID;

    /**
     * The object containing the model type.
     *
     * @var string $modelType
     */
    public string $modelType;

    /**
     * The user's current rating of the model.
     *
     * @var null|float $rating
     */
    public ?float $rating = null;

    /**
     * The written review text.
     *
     * @var null|string $reviewText
     */
    public ?string $reviewText = null;

    /**
     * The written note text.
     *
     * @var null|string $noteText
     */
    public ?string $noteText = null;

    /**
     * Whether the detailed review form is shown.
     *
     * @var bool $isDetailed
     */
    public bool $isDetailed = false;

    /**
     * The user's per-category scores keyed by rating category id.
     *
     * @var array $scores
     */
    public array $scores = [];

    /**
     * The user's per-category review texts keyed by rating category id.
     *
     * @var array $categoryReviews
     */
    public array $categoryReviews = [];

    /**
     * Whether the removal confirmation is shown.
     *
     * @var bool $confirmingRemoval
     */
    public bool $confirmingRemoval = false;

    /**
     * @var bool $showPopup
     */
    public bool $showPopup = false;

    /**
     * The component's listeners.
     *
     * @var array
     */
    protected $listeners = [
        'show-review-box' => 'handleShowReviewBox',
    ];

    /**
     * @param string           $reviewBoxId
     * @param string           $modelId
     * @param string           $modelType
     * @param null|MediaRating $userRating
     *
     * @return void
     */
    public function mount(string $reviewBoxId, string $modelId, string $modelType, $userRating): void
    {
        $this->reviewBoxID = $reviewBoxId;
        $this->modelID = $modelId;
        $this->modelType = $modelType;
        $this->rating = $userRating?->rating;
    }

    /**
     * Handles showing the review box to the user.
     *
     * @param $id
     *
     * @return void
     */
    public function handleShowReviewBox($id): void
    {
        if ($id == $this->reviewBoxID) {
            $this->showReviewBox();
        }
    }

    /**
     * Shows the review box to the user.
     *
     * @return RedirectResponse|void
     */
    public function showReviewBox()
    {
        // Require user to authenticate if necessary.
        if (!auth()->check()) {
            return to_route('sign-in');
        }

        $user = auth()->user();
        $user->loadMissing('settings');

        $mediaRating = $user->mediaRatings()
            ->where([
                ['model_id', '=', $this->modelID],
                ['model_type', '=', $this->modelType],
            ])
            ->with('categoryScores')
            ->first();

        $this->rating = $mediaRating?->rating;
        $this->reviewText = $mediaRating?->description ?? '';
        $this->noteText = $mediaRating?->note ?? '';
        $this->isDetailed = ($user->settings?->rating_style ?? RatingStyle::Standard())->is(RatingStyle::Detailed)
            && RatingCategory::where('model_type', '=', $this->modelType)->exists();

        if ($this->isDetailed) {
            $this->loadCategoryScores($mediaRating);
        }

        $this->showPopup = true;
    }

    /**
     * Loads the user's per-category scores and reviews.
     *
     * @param null|MediaRating $mediaRating
     *
     * @return void
     */
    protected function loadCategoryScores(?MediaRating $mediaRating): void
    {
        $existingScores = $mediaRating?->categoryScores
            ->pluck('score', 'rating_category_id') ?? collect();
        $existingReviews = $mediaRating?->categoryScores
            ->pluck('review', 'rating_category_id') ?? collect();
        $defaultScore = ($mediaRating?->rating ?? 0) > 0
            ? $mediaRating->rating * 2
            : RatingCategoryScore::MAX_SCORE_VALUE / 2;

        foreach ($this->categories as $ratingCategory) {
            $this->scores[(string) $ratingCategory->id] = (float) ($existingScores[$ratingCategory->id] ?? $defaultScore);
            $this->categoryReviews[(string) $ratingCategory->id] = (string) ($existingReviews[$ratingCategory->id] ?? '');
        }
    }

    /**
     * Submits the written review.
     *
     * @return void
     */
    public function submitReview(): void
    {
        $reviewText = strip_tags($this->reviewText);
        $noteText = strip_tags($this->noteText);

        $reviewText = empty($reviewText) ? null : $reviewText;
        $noteText = empty($noteText) ? null : $noteText;

        if ($this->isDetailed) {
            $this->submitDetailedReview($noteText);
            return;
        }

        auth()->user()->mediaRatings()->withoutGlobalScopes()
            ->updateOrCreate([
                'model_type' => $this->modelType,
                'model_id' => $this->modelID,
            ], [
                'description' => $reviewText,
                'note' => $noteText,
            ]);

        UserLibraryTouch::touch(auth()->id(), $this->modelType, [$this->modelID]);

        $this->showPopup = false;

        $this->dispatch('review-submitted');
    }

    /**
     * Submits the detailed review.
     *
     * @param null|string $noteText
     *
     * @return void
     */
    protected function submitDetailedReview(?string $noteText): void
    {
        $totalWeight = 0.0;
        $weightedSum = 0.0;
        $validScores = [];
        $reviewParts = [];

        foreach ($this->categories as $ratingCategory) {
            $score = (float) ($this->scores[(string) $ratingCategory->id] ?? RatingCategoryScore::MAX_SCORE_VALUE / 2);
            $score = max(RatingCategoryScore::MIN_SCORE_VALUE, min(RatingCategoryScore::MAX_SCORE_VALUE, $score));
            $review = strip_tags(trim((string) ($this->categoryReviews[(string) $ratingCategory->id] ?? '')));
            $review = empty($review) ? null : $review;

            $validScores[$ratingCategory->id] = [
                'score' => $score,
                'review' => $review,
            ];
            $weightedSum += $score * $ratingCategory->weight;
            $totalWeight += $ratingCategory->weight;

            if ($review !== null) {
                $reviewParts[] = $ratingCategory->name . ': ' . $review;
            }
        }

        $overallScore = $totalWeight > 0 ? $weightedSum / $totalWeight : RatingCategoryScore::MAX_SCORE_VALUE / 2;
        $this->rating = round($overallScore / 2, 2);

        // The review is inferred from the per-category texts.
        $reviewText = empty($reviewParts) ? null : implode("\n\n", $reviewParts);

        // Update or create the authenticated user's rating for this model.
        $mediaRating = auth()->user()->mediaRatings()->withoutGlobalScopes()
            ->updateOrCreate([
                'model_type' => $this->modelType,
                'model_id' => $this->modelID,
            ], [
                'rating' => $this->rating,
                'description' => $reviewText,
                'note' => $noteText,
            ]);

        foreach ($validScores as $ratingCategoryID => $categoryScore) {
            RatingCategoryScore::updateOrCreate([
                'rating_id' => $mediaRating->id,
                'rating_category_id' => $ratingCategoryID,
            ], $categoryScore);
        }

        UserLibraryTouch::touch(auth()->id(), $this->modelType, [$this->modelID]);

        $this->showPopup = false;

        $this->dispatch('review-submitted');
        $this->dispatch('star-rating-updated-' . $this->modelID . '-' . $this->modelType, id: $this->getID(), modelID: $this->modelID, modelType: $this->modelType, rating: $this->rating);
    }

    /**
     * Removes the authenticated user's rating of the model.
     *
     * @return void
     */
    public function removeRating(): void
    {
        // Delete through the model so observers fire.
        auth()->user()->mediaRatings()->where([
            ['model_id', '=', $this->modelID],
            ['model_type', '=', $this->modelType],
        ])->first()?->delete();

        $this->rating = null;
        $this->reviewText = '';
        $this->noteText = '';
        $this->scores = [];
        $this->categoryReviews = [];
        $this->confirmingRemoval = false;

        UserLibraryTouch::touch(auth()->id(), $this->modelType, [$this->modelID]);

        $this->showPopup = false;

        $this->dispatch('review-submitted');
        $this->dispatch('star-rating-updated-' . $this->modelID . '-' . $this->modelType, id: $this->getID(), modelID: $this->modelID, modelType: $this->modelType, rating: null);
    }

    /**
     * Get the rating categories of the model type.
     *
     * @return Collection
     */
    public function getCategoriesProperty(): Collection
    {
        if (!$this->isDetailed) {
            return collect();
        }

        return RatingCategory::forModelType($this->modelType)
            ->get();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.review-box');
    }
}
