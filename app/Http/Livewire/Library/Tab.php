<?php

namespace App\Http\Livewire\Library;

use App\Enums\UserLibraryStatus;
use Auth;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Tab extends Component
{
    use WithPagination;

    /**
     * The component's filters.
     *
     * @var array $filter
     */
    public array $filter = [
        'search' => '',
        'order_type' => '',
        'per_page' => 25,
    ];

    /**
     * The user library status string.
     *
     * @var string $userLibraryStatusString
     */
    public string $userLibraryStatusString;

    /**
     * Whether to load the resource.
     *
     * @var bool $loadResourceIsEnabled
     */
    public bool $loadResourceIsEnabled = false;

    /**
     * Prepare the component.
     *
     * @param UserLibraryStatus $userLibraryStatus
     * @return void
     */
    public function mount(UserLibraryStatus $userLibraryStatus): void
    {
        $this->userLibraryStatusString = $userLibraryStatus->key;
    }

    /**
     * Enable resource loading.
     *
     * @return void
     */
    public function loadResource(): void
    {
        $this->loadResourceIsEnabled = true;
    }

    /**
     * Get user library for the specified library status.
     *
     * @return LengthAwarePaginator|null
     * @throws InvalidEnumKeyException
     */
    private function getLibrary(): ?LengthAwarePaginator
    {
        if (!$this->loadResourceIsEnabled) {
            return null;
        }

        // Get library status
        $userLibraryStatus = UserLibraryStatus::fromKey($this->userLibraryStatusString);

        // Get library items
        $library = Auth::user()
            ->library()
            ->wherePivot('status', $userLibraryStatus->value);

        // Search
        if (!empty($this->filter['search'])) {
            $library = $library->whereTranslationLike('title', '%' . $this->filter['search'] . '%');
        }

        // Order
        if (!empty($this->filter['order_type'])) {
            $library = $library->orderByTranslation('title', $this->filter['order_type']);
        }

        // Paginate
        return $library->paginate($this->filter['per_page'] ?? 25);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     * @throws InvalidEnumKeyException
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.library.tab', [
            'library' => $this->getLibrary()
        ]);
    }
}
