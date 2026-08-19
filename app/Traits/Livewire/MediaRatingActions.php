<?php

namespace App\Traits\Livewire;

use App\Enums\ParentalGuideReaction;
use App\Enums\ReportReason;
use App\Models\MediaRating;
use App\Models\Report;
use Illuminate\Validation\Rule;

trait MediaRatingActions
{
    /**
     * Optimistic vote overrides keyed by media rating id.
     *
     * @var array<int, array{helpful: bool|null, helpfulCount: int, unhelpfulCount: int}> $voteOverrides
     */
    public array $voteOverrides = [];

    /**
     * Whether the review report modal is open.
     *
     * @var bool $confirmingReviewReport
     */
    public bool $confirmingReviewReport = false;

    /**
     * The id of the review being reported.
     *
     * @var int|null $reportRatingID
     */
    public ?int $reportRatingID = null;

    /**
     * The selected reason key.
     *
     * @var string $reportReasonKey
     */
    public string $reportReasonKey = ReportReason::NotAReview;

    /**
     * The optional free-text details.
     *
     * @var string $reportDetails
     */
    public string $reportDetails = '';

    /**
     * Returns the reasons offered when reporting a review, keyed by reason key.
     *
     * @return array<string, string>
     */
    public function getReviewReportReasonsProperty(): array
    {
        return collect(ReportReason::offeredForReview())
            ->mapWithKeys(fn (string $reason) => [$reason => ReportReason::getDescription($reason)])
            ->all();
    }

    /**
     * Opens the report modal for the given review.
     *
     * @param int $ratingID
     *
     * @return void
     */
    public function openReviewReportForm(int $ratingID): void
    {
        if (auth()->user() === null) {
            $this->redirect(route('sign-in'));
            return;
        }

        $this->reportRatingID = $ratingID;
        $this->reportReasonKey = ReportReason::NotAReview;
        $this->reportDetails = '';

        $this->confirmingReviewReport = true;
    }

    /**
     * Submits the review report.
     *
     * @return void
     */
    public function submitReviewReport(): void
    {
        $user = auth()->user();

        if ($user === null) {
            $this->redirect(route('sign-in'));
            return;
        }

        if ($this->reportRatingID === null) {
            return;
        }

        $mediaRating = MediaRating::withoutGlobalScopes()
            ->find($this->reportRatingID);

        if ($mediaRating === null) {
            return;
        }

        $validated = $this->validate([
            'reportReasonKey' => ['bail', 'required', 'string', Rule::in(ReportReason::offeredForReview())],
            'reportDetails' => ['bail', 'nullable', 'string', 'max:1000', 'required_if:reportReasonKey,other'],
        ]);

        $isOwnReview = (int) $mediaRating->user_id === $user->id;
        $alreadyReported = Report::where('reportable_type', '=', $mediaRating->getMorphClass())
            ->where('reportable_id', '=', $mediaRating->getKey())
            ->where('user_id', '=', $user->id)
            ->exists();

        if (!$isOwnReview && !$alreadyReported) {
            Report::create([
                'reportable_type' => $mediaRating->getMorphClass(),
                'reportable_id' => $mediaRating->getKey(),
                'user_id' => $user->id,
                'reason_key' => $validated['reportReasonKey'],
                'details' => $validated['reportDetails'] !== '' ? $validated['reportDetails'] : null,
            ]);
        }

        $this->confirmingReviewReport = false;
        $this->reportRatingID = null;
        $this->reportReasonKey = ReportReason::NotAReview;
        $this->reportDetails = '';
    }

    /**
     * Toggles the (un)helpful vote on a review, applying an optimistic count update so the UI
     * reflects the change immediately.
     *
     * @param int    $ratingID
     * @param string $direction Either `helpful` or `unhelpful`.
     *
     * @return void
     */
    public function voteOnReview(int $ratingID, string $direction): void
    {
        $user = auth()->user();

        if ($user === null) {
            $this->redirect(route('sign-in'));
            return;
        }

        $mediaRating = MediaRating::withoutGlobalScopes()
            ->find($ratingID);

        if ($mediaRating === null) {
            return;
        }

        if ((int) $mediaRating->user_id === $user->id) {
            return;
        }

        // Ratings created before reactions shipped aren't registered as reactants yet.
        if ($mediaRating->isNotRegisteredAsLoveReactant()) {
            $mediaRating->registerAsLoveReactant();
            $mediaRating->refresh();
        }

        $mediaRating->load(MediaRating::lockupEagerLoads($user));
        $current = $user->getHelpfulnessFor($mediaRating);
        $oldHelpful = $current === null ? null : $current->is(ParentalGuideReaction::Helpful());
        $tappedHelpful = match ($direction) {
            'helpful' => true,
            'unhelpful' => false,
            default => null,
        };

        $predicted = ($oldHelpful === $tappedHelpful) ? null : $tappedHelpful;

        $existingOverride = $this->voteOverrides[$ratingID] ?? null;
        $helpfulCount = $existingOverride['helpfulCount'] ?? $mediaRating->helpful_count;
        $unhelpfulCount = $existingOverride['unhelpfulCount'] ?? $mediaRating->unhelpful_count;

        if ($oldHelpful !== $predicted) {
            if ($oldHelpful === true) {
                $helpfulCount = max(0, $helpfulCount - 1);
            } elseif ($oldHelpful === false) {
                $unhelpfulCount = max(0, $unhelpfulCount - 1);
            }

            if ($predicted === true) {
                $helpfulCount++;
            } elseif ($predicted === false) {
                $unhelpfulCount++;
            }
        }

        $this->voteOverrides[$ratingID] = [
            'helpful' => $predicted,
            'helpfulCount' => $helpfulCount,
            'unhelpfulCount' => $unhelpfulCount,
        ];

        $reaction = match ($predicted) {
            true => ParentalGuideReaction::Helpful(),
            false => ParentalGuideReaction::Unhelpful(),
            default => null,
        };

        $user->setHelpfulness($mediaRating, $reaction);
    }

    /**
     * Toggles whether a review holds the item's Editor's Choice slot.
     *
     * @param int $ratingID
     *
     * @return void
     */
    public function elevateReview(int $ratingID): void
    {
        $user = auth()->user();

        if ($user === null || !$user->can('elevateMediaRating')) {
            return;
        }

        $mediaRating = MediaRating::withoutGlobalScopes()
            ->find($ratingID);

        if ($mediaRating === null || trim((string) $mediaRating->description) === '') {
            return;
        }

        if ($mediaRating->is_elevated) {
            $mediaRating->update([
                'is_elevated' => false,
                'elevated_at' => null,
                'elevated_by_user_id' => null,
            ]);

            return;
        }

        // The slot holds one review, so the review that held it steps down.
        MediaRating::withoutGlobalScopes()
            ->where('model_type', '=', $mediaRating->model_type)
            ->where('model_id', '=', $mediaRating->model_id)
            ->where('is_elevated', '=', true)
            ->update([
                'is_elevated' => false,
                'elevated_at' => null,
                'elevated_by_user_id' => null,
            ]);

        $mediaRating->update([
            'is_elevated' => true,
            'elevated_at' => now(),
            'elevated_by_user_id' => $user->id,
        ]);
    }
}
