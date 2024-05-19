<?php

namespace App\Livewire\Legal;

use Carbon\Carbon;
use File;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Markdown;

class TermsOfUse extends Component
{
    /**
     * The terms of use text.
     *
     * @var string $termsOfUse
     */
    public string $termsOfUse = '';

    /**
     * Prepare the component.
     *
     * @throws FileNotFoundException
     *
     * @return void
     */
    public function mount()
    {
        $filePath = resource_path('docs/terms_of_use.md');

        // Get the last update date.
        $lastUpdateUnix = Carbon::createFromTimestamp(File::lastModified($filePath));
        $lastUpdateStr = $lastUpdateUnix->format('F d, Y');

        // Attach date.
        $termsOfUseContent = str_replace('#UPDATE_DATE#', $lastUpdateStr, File::get($filePath));

        // Convert Markdown to HTML.
        $this->termsOfUse = Markdown::parse($termsOfUseContent);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.legal.terms-of-use');
    }
}
