<?php

namespace App\Http\Livewire\Legal;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Livewire\Component;

class TermsOfUse extends Component
{
    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render()
    {
        $privacyPolicyRequest = Request::create('/api/v1/legal/terms-of-use');
        $responseData = (array) Route::dispatch($privacyPolicyRequest)->getData();

        return view('livewire.legal.terms-of-use', [
            'termsOfUseText'    => $responseData['data']->attributes->text
        ]);
    }
}
