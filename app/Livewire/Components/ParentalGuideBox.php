<?php

namespace App\Livewire\Components;

use App\Traits\Livewire\ParentalGuideSubmission;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Livewire\Attributes\Isolate;
use Livewire\Attributes\On;
use Livewire\Component;

#[Isolate]
class ParentalGuideBox extends Component
{
    use ParentalGuideSubmission;

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
     * Prepare the component.
     *
     * @param string $modelId
     * @param string $modelType
     *
     * @return void
     */
    public function mount(string $modelId, string $modelType): void
    {
        $this->modelID = $modelId;
        $this->modelType = $modelType;
    }

    /**
     * Opens the box on a new entry, or on the entry being edited.
     *
     * @param int|null $category
     * @param int|null $entry
     *
     * @return void
     * @throws AuthorizationException
     */
    #[On('parental-guide-box-open')]
    public function open(?int $category = null, ?int $entry = null): void
    {
        if (auth()->user() === null) {
            $this->redirectRoute('sign-in', navigate: true);
            return;
        }

        if ($entry !== null) {
            $this->openEditForm($entry);
            return;
        }

        $this->openSubmitForm($category);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.parental-guide-box');
    }

    /**
     * @inheritDoc
     */
    protected function submissionTargetModel(): Model
    {
        $modelClass = Relation::getMorphedModel($this->modelType) ?? $this->modelType;

        return $modelClass::findOrFail($this->modelID);
    }

    /**
     * @inheritDoc
     */
    protected function afterSubmit(): void
    {
        $this->dispatch('parental-guide-updated');
    }
}
