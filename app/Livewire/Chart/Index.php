<?php

namespace App\Livewire\Chart;

use App\Enums\ChartKind;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Component;

class Index extends Component
{
    /**
     * The available chart kinds.
     *
     * @var array $chartKinds
     */
    public array $chartKinds;

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount(): void
    {
        $this->chartKinds = ChartKind::getValues();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.chart.index');
    }
}
