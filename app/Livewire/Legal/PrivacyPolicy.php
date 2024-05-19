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

class PrivacyPolicy extends Component
{
    /**
     * The privacy policy text.
     *
     * @var string $termsOfUse
     */
    public string $privacyPolicy = '';

    /**
     * Prepare the component.
     *
     * @throws FileNotFoundException
     *
     * @return void
     */
    public function mount()
    {
        $filePath = resource_path('docs/privacy_policy.md');

        // Get the last update date.
        $lastUpdateUnix = Carbon::createFromTimestamp(File::lastModified($filePath));
        $lastUpdateStr = $lastUpdateUnix->format('F d, Y');

        // Attach date.
        $privacyPolicyContent = str_replace('#UPDATE_DATE#', $lastUpdateStr, File::get($filePath));

        // Convert Markdown to HTML.
        $this->privacyPolicy = Markdown::parse($privacyPolicyContent);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.legal.privacy-policy');
    }
}
