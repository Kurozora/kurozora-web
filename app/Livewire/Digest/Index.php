<?php

namespace App\Livewire\Digest;

use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
{
    /**
     * An optional reference date used to preview the digest of a specific week.
     *
     * @var string|null $reference
     */
    public ?string $reference = null;

    /**
     * The query strings of the component.
     *
     * @return array
     */
    protected function queryString(): array
    {
        return [
            'reference' => ['except' => null],
        ];
    }

    /**
     * The label for the digest's week range.
     *
     * @return string
     */
    public function getWindowLabelProperty(): string
    {
        $reference = rescue(fn () => $this->reference ? Carbon::parse($this->reference) : null, null, false) ?? Carbon::now();
        $windowEnd = $reference->copy()->startOfWeek(Carbon::MONDAY);
        $windowStart = $windowEnd->copy()->subWeek();

        return __(':start – :end', [
            'start' => $windowStart->isoFormat('MMM D'),
            'end' => $windowEnd->copy()->subDay()->isoFormat('MMM D'),
        ]);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.digest.index');
    }
}
