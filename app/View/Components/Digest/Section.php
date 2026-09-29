<?php

namespace App\View\Components\Digest;

use App\Services\WeeklyDigestService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Section extends Component
{
    /**
     * The type of the section.
     *
     * @var string $type
     */
    public string $type;

    /**
     * The content of the section.
     *
     * @var array $section
     */
    public array $section;

    /**
     * Create a new component instance.
     *
     * @param string      $type
     * @param string|null $reference
     */
    public function __construct(string $type, ?string $reference = null)
    {
        $this->type = $type;
        $referenceDate = rescue(fn () => $reference ? Carbon::parse($reference) : null, null, false);
        $this->section = app(WeeklyDigestService::class)->buildSection(auth()->user(), $type, $referenceDate);
    }

    /**
     * Get the view that represents the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.digest.section');
    }
}
