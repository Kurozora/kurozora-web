<?php

namespace App\Http\Livewire\ThemeStore;

use App\Models\AppTheme;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
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
     * Prepare the component.
     *
     * @return void
     */
    public function mount() {}

    /**
     * The computed platform themes property.
     *
     * @return LengthAwarePaginator
     */
    private function getPlatformThemesAttribute(): LengthAwarePaginator
    {
        $platformThemes = AppTheme::query();

        // Search
        if (!empty($this->filter['search'])) {
            $platformThemes = $platformThemes->where('name', 'like', '%' . $this->filter['search'] . '%');
        }

        // Order
        if (!empty($this->filter['order_type'])) {
            $platformThemes = $platformThemes->orderBy('name', $this->filter['order_type']);
        }

        // Paginate
        return $platformThemes->paginate($this->filter['per_page'] ?? 25);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.theme-store.index', [
            'platformThemes' => $this->getPlatformThemesAttribute()
        ]);
    }
}
