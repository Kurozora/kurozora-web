<?php

namespace App\Traits\Livewire;

use App\Enums\ParentalGuideReaction;
use App\Enums\ParentalGuideReportReason;
use App\Models\ParentalGuideEntry;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Auth\Access\AuthorizationException;

trait ParentalGuideEntryActions
{
    /**
     * Whether the report modal is open.
     *
     * @var bool $confirmingReport
     */
    public bool $confirmingReport = false;

    /**
     * The id of the entry being reported.
     *
     * @var int|null $reportEntryID
     */
    public ?int $reportEntryID = null;

    /**
     * The selected reason key.
     *
     * @var string $reportReasonKey
     */
    public string $reportReasonKey = ParentalGuideReportReason::Inaccurate;

    /**
     * The optional free-text details.
     *
     * @var string $reportDetails
     */
    public string $reportDetails = '';

    /**
     * Optimistic vote overrides keyed by entry id.
     *
     * @var array<int, array{helpful: bool|null, helpfulCount: int, unhelpfulCount: int}> $voteOverrides
     */
    public array $voteOverrides = [];

    /**
     * Refreshes the rendered state after a delete.
     *
     * @return void
     */
    abstract protected function afterEntryDeleted(): void;

    /**
     * Opens the report modal for the given entry.
     *
     * @param int $entryID
     *
     * @return void
     */
    public function openReportForm(int $entryID): void
    {
        if (auth()->user() === null) {
            $this->redirect(route('login'));
            return;
        }

        $this->reportEntryID = $entryID;
        $this->reportReasonKey = ParentalGuideReportReason::Inaccurate;
        $this->reportDetails = '';

        $this->confirmingReport = true;
    }

    /**
     * Submits the report.
     *
     * @return void
     */
    public function submitReport(): void
    {
        $user = auth()->user();

        if ($user === null) {
            $this->redirect(route('login'));
            return;
        }

        if ($this->reportEntryID === null) {
            return;
        }

        $entry = ParentalGuideEntry::find($this->reportEntryID);

        if ($entry === null) {
            return;
        }

        $validated = $this->validate([
            'reportReasonKey' => ['bail', 'required', 'string', new EnumValue(ParentalGuideReportReason::class, false)],
            'reportDetails' => ['bail', 'nullable', 'string', 'max:1000', 'required_if:reportReasonKey,other'],
        ]);

        $entry->reports()->create([
            'user_id' => $user->id,
            'reason_key' => $validated['reportReasonKey'],
            'details' => $validated['reportDetails'] !== '' ? $validated['reportDetails'] : null,
        ]);

        $this->confirmingReport = false;
        $this->reportEntryID = null;
        $this->reportReasonKey = ParentalGuideReportReason::Inaccurate;
        $this->reportDetails = '';
    }

    /**
     * Toggles the (un)helpful vote on an entry, applying an optimistic count update so the UI
     * reflects the change immediately.
     *
     * @param int    $entryID
     * @param string $direction Either `helpful` or `unhelpful`.
     *
     * @return void
     */
    public function vote(int $entryID, string $direction): void
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

        $entry->load(ParentalGuideEntry::lockupEagerLoads($user));
        $current = $user->getHelpfulnessFor($entry);
        $oldHelpful = $current === null ? null : $current->is(ParentalGuideReaction::Helpful());
        $tappedHelpful = match ($direction) {
            'helpful'   => true,
            'unhelpful' => false,
            default     => null,
        };

        $predicted = ($oldHelpful === $tappedHelpful) ? null : $tappedHelpful;

        $existingOverride = $this->voteOverrides[$entryID] ?? null;
        $helpfulCount = $existingOverride['helpfulCount'] ?? $entry->helpful_count;
        $unhelpfulCount = $existingOverride['unhelpfulCount'] ?? $entry->unhelpful_count;

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

        $this->voteOverrides[$entryID] = [
            'helpful' => $predicted,
            'helpfulCount' => $helpfulCount,
            'unhelpfulCount' => $unhelpfulCount,
        ];

        $reaction = match ($predicted) {
            true    => ParentalGuideReaction::Helpful(),
            false   => ParentalGuideReaction::Unhelpful(),
            default => null,
        };

        $user->setHelpfulness($entry, $reaction);
    }

    /**
     * Deletes the given entry.
     *
     * @param int $entryID
     *
     * @return void
     *
     * @throws AuthorizationException
     */
    public function deleteEntry(int $entryID): void
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

        if (!$user->can('delete', $entry)) {
            throw new AuthorizationException();
        }

        $entry->delete();

        $this->afterEntryDeleted();
    }
}
