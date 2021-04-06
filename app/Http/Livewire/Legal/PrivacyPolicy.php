<?php

namespace App\Http\Livewire\Legal;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Livewire\Component;

class PrivacyPolicy extends Component
{
    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): View|Factory|Application
    {
        $privacyPolicyRequest = Request::create('/api/v1/legal/privacy-policy');
        $responseData = (array) Route::dispatch($privacyPolicyRequest)->getData();

        return view('livewire.legal.privacy-policy', [
            'privacyPolicyText'    => $responseData['data']->attributes->text
        ]);
    }
}
