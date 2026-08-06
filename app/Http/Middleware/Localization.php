<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response|RedirectResponse) $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $user = auth()->user();

        // Check header request and determine localization
        if ($user !== null) {
            $locale = $user->language_id ?? 'en';
        } else if ($request->hasHeader('X-Localization')) {
            $locale = $request->header('X-Localization');
        } else if (session()->has('locale')) {
            $locale = session('locale');
        } else {
            $locale = 'en';
        }

        // Set Laravel localization
        app()->setLocale($locale);

        // Continue request
        return $next($request);
    }
}
