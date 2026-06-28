<?php

namespace App\Livewire\Digest;

use App\Services\WeeklyDigestService;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Isolate;
use Livewire\Component;

#[Isolate]
class Section extends Component
{
    /**
     * The section type to build and render.
     *
     * @var string $type
     */
    public string $type;

    /**
     * An optional reference date used to preview a specific week.
     *
     * @var string|null $reference
     */
    public ?string $reference = null;

    /**
     * Whether the section is ready to load, so the page can paint before building it.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * The built section data, memoized for the request.
     *
     * @var array|null $sectionData
     */
    private ?array $sectionData = null;

    /**
     * Prepare the component.
     *
     * @param string      $type
     * @param string|null $reference
     *
     * @return void
     */
    public function mount(string $type, ?string $reference = null): void
    {
        $this->type = $type;
        $this->reference = $reference;
    }

    /**
     * Load the section on a follow-up request so the page paints immediately.
     *
     * @return void
     */
    public function loadSection(): void
    {
        $this->readyToLoad = true;
    }

    /**
     * The built data for this section.
     *
     * @return array
     */
    public function getDataProperty(): array
    {
        if (!$this->readyToLoad) {
            return [];
        }

        if ($this->sectionData === null) {
            $reference = rescue(fn () => $this->reference ? Carbon::parse($this->reference) : null, null, false);
            $this->sectionData = app(WeeklyDigestService::class)->buildSection(auth()->user(), $this->type, $reference);
        }

        return $this->sectionData;
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.digest.section');
    }
}
