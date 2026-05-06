<?php

namespace App\Traits\Livewire;

use App\Enums\ParentalGuideCategory;
use App\Enums\ParentalGuideDepiction;
use App\Enums\ParentalGuideFrequency;
use App\Enums\ParentalGuideRating;
use App\Jobs\UpdateParentalGuideStatsJob;
use App\Models\ParentalGuideEntry;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;

trait ParentalGuideSubmission
{
    /**
     * Whether the submit modal is open.
     *
     * @var bool $confirmingSubmit
     */
    public bool $confirmingSubmit = false;

    /**
     * The id of the entry being edited.
     *
     * @var int|null $editEntryID
     */
    public ?int $editEntryID = null;

    /**
     * The category being submitted, or `null` until chosen.
     *
     * @var int|null $submitCategory
     */
    public ?int $submitCategory = null;

    /**
     * The severity rating value.
     *
     * @var int $submitRating
     */
    public int $submitRating = 0;

    /**
     * The frequency value.
     *
     * @var int $submitFrequency
     */
    public int $submitFrequency = 1;

    /**
     * The depiction value, or `null` for categories that don't support depiction.
     *
     * @var int|null $submitDepiction
     */
    public ?int $submitDepiction = 1;

    /**
     * The free-text reason.
     *
     * @var string $submitReason
     */
    public string $submitReason = '';

    /**
     * Whether the reason is flagged as a spoiler.
     *
     * @var bool $submitIsSpoiler
     */
    public bool $submitIsSpoiler = false;

    /**
     * The model that owns the entries (Anime / Manga / Game).
     *
     * @return Model
     */
    abstract protected function submissionTargetModel(): Model;

    /**
     * Hook for the host component to refresh its rendered state after a successful submit.
     *
     * @return void
     */
    abstract protected function afterSubmit(): void;

    /**
     * Open the submit modal in empty state.
     *
     * @param int|null $category Optional category to preselect.
     *
     * @return void
     */
    public function openSubmitForm(?int $category = null): void
    {
        if (auth()->user() === null) {
            $this->redirect(route('login'));
            return;
        }

        $this->resetSubmitState();
        $this->submitCategory = $category;
        $this->confirmingSubmit = true;
    }

    /**
     * Open the submit modal seeded from an existing entry.
     *
     * @param int $entryID
     *
     * @return void
     * @throws AuthorizationException
     */
    public function openEditForm(int $entryID): void
    {
        $user = auth()->user();

        if ($user === null) {
            $this->redirect(route('login'));
            return;
        }

        $entry = ParentalGuideEntry::find($entryID);

        if ($entry === null) {
            return;
        }

        if (!$user->can('update', $entry)) {
            throw new AuthorizationException();
        }

        $this->resetSubmitState();
        $this->editEntryID = $entry->id;
        $this->submitCategory = (int) $entry->category->value;
        $this->submitRating = (int) ($entry->rating?->value ?? 0);
        $this->submitFrequency = (int) ($entry->frequency?->value ?? 1);
        $this->submitDepiction = $entry->depiction?->value !== null ? (int) $entry->depiction->value : 1;
        $this->submitReason = (string) ($entry->reason ?? '');
        $this->submitIsSpoiler = (bool) $entry->is_spoiler;
        $this->confirmingSubmit = true;
    }

    /**
     * Validate, persist, and close the modal.
     *
     * @return void
     * @throws AuthorizationException
     */
    public function submitEntry(): void
    {
        $user = auth()->user();

        if ($user === null) {
            $this->redirect(route('login'));
            return;
        }

        $hasSeverity = (int) $this->submitRating !== ParentalGuideRating::None;

        $this->validate([
            'submitCategory' => ['bail', 'required', 'integer', new EnumValue(ParentalGuideCategory::class, false)],
            'submitRating' => ['bail', 'required', 'integer', new EnumValue(ParentalGuideRating::class, false)],
            'submitFrequency' => ['bail', $hasSeverity ? 'required' : 'nullable', 'integer', new EnumValue(ParentalGuideFrequency::class, false)],
            'submitDepiction' => ['bail', 'nullable', 'integer', new EnumValue(ParentalGuideDepiction::class, false)],
            'submitReason' => ['bail', 'nullable', 'string', 'max:500'],
            'submitIsSpoiler' => ['bail', 'required', 'boolean'],
        ]);

        $supportsDepiction = $hasSeverity && $this->categorySupportsDepiction((int) $this->submitCategory);
        $frequency = $hasSeverity ? $this->submitFrequency : null;
        $depiction = $supportsDepiction ? $this->submitDepiction : null;
        $reason = $this->submitReason !== '' ? $this->submitReason : null;

        $model = $this->submissionTargetModel();

        if ($this->editEntryID !== null) {
            $entry = ParentalGuideEntry::find($this->editEntryID);

            if ($entry === null) {
                return;
            }

            if (!$user->can('update', $entry)) {
                throw new AuthorizationException();
            }

            $entry->update([
                'category' => (int) $this->submitCategory,
                'rating' => $this->submitRating,
                'frequency' => $frequency,
                'depiction' => $depiction,
                'reason' => $reason,
                'is_spoiler' => $this->submitIsSpoiler,
            ]);
        } else {
            ParentalGuideEntry::create([
                'user_id' => $user->id,
                'model_type' => $model->getMorphClass(),
                'model_id' => $model->getKey(),
                'category' => (int) $this->submitCategory,
                'rating' => $this->submitRating,
                'frequency' => $frequency,
                'depiction' => $depiction,
                'reason' => $reason,
                'is_spoiler' => $this->submitIsSpoiler,
            ]);
        }

        UpdateParentalGuideStatsJob::dispatch($model->getMorphClass(), $model->getKey());

        $this->confirmingSubmit = false;
        $this->resetSubmitState();
        $this->afterSubmit();
    }

    /**
     * Whether the given category accepts a depiction value.
     *
     * @param int $category
     *
     * @return bool
     */
    protected function categorySupportsDepiction(int $category): bool
    {
        return match ($category) {
            ParentalGuideCategory::SexAndNudity,
            ParentalGuideCategory::ViolenceAndGore,
            ParentalGuideCategory::FrighteningAndIntenseScenes => true,
            default => false,
        };
    }

    private function resetSubmitState(): void
    {
        $this->editEntryID = null;
        $this->submitCategory = null;
        $this->submitRating = 0;
        $this->submitFrequency = 1;
        $this->submitDepiction = 1;
        $this->submitReason = '';
        $this->submitIsSpoiler = false;
    }
}
