<?php

namespace App\Traits\Livewire;

use App\Enums\ParentalGuideCategory;
use App\Models\ParentalGuideEntry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\CursorPaginator;
use Livewire\WithPagination;

trait ParentalGuideCategoryListing
{
    use ParentalGuideEntryActions;
    use ParentalGuideSubmission;
    use WithPagination;

    /**
     * The slug of the category being listed.
     *
     * @var string|null $categorySlug
     */
    public ?string $categorySlug = null;

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * Returns the media model the entries belong to.
     *
     * @return Model
     */
    abstract protected function listingTargetModel(): Model;

    /**
     * Resolves the category from its slug.
     *
     * @param string $slug
     *
     * @return void
     */
    protected function resolveCategoryFromSlug(string $slug): void
    {
        if (ParentalGuideCategory::fromSlug($slug) === null) {
            abort(404);
        }

        $this->categorySlug = $slug;
    }

    /**
     * The resolved category enum.
     *
     * @return ParentalGuideCategory|null
     */
    public function getCategoryProperty(): ?ParentalGuideCategory
    {
        if ($this->categorySlug === null) {
            return null;
        }

        return ParentalGuideCategory::fromSlug($this->categorySlug);
    }

    /**
     * Marks the component ready to load.
     *
     * @return void
     */
    public function loadPage(): void
    {
        $this->readyToLoad = true;
    }

    /**
     * Returns the paginated entries for the bound category.
     *
     * @return CursorPaginator
     */
    public function getEntriesProperty(): CursorPaginator
    {
        $category = $this->category;

        if (!$this->readyToLoad || $category === null) {
            return new CursorPaginator(collect(), 20);
        }

        $authUser = auth()->user();
        $model = $this->listingTargetModel();

        return ParentalGuideEntry::query()
            ->visible()
            ->withReason()
            ->where('model_type', '=', $model->getMorphClass())
            ->where('model_id', '=', $model->getKey())
            ->where('category', '=', $category->value)
            ->with(ParentalGuideEntry::lockupEagerLoads($authUser))
            ->orderByDesc('created_at')
            ->cursorPaginate(20);
    }

    /**
     * @inheritDoc
     */
    protected function afterEntryDeleted(): void
    {
        $this->resetPage();
    }

    /**
     * @inheritDoc
     */
    protected function submissionTargetModel(): Model
    {
        return $this->listingTargetModel();
    }

    /**
     * @inheritDoc
     */
    protected function afterSubmit(): void
    {
        $this->resetPage();
    }
}
