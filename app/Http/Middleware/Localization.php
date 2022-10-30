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
        // Check header request and determine localization
        if (auth()->check()) {
            $locale = auth()->user()->language_id;
        } elseif ($request->hasHeader('X-Localization')) {
            $locale = $request->header('X-Localization');
        } elseif (session()->has('locale')) {
            $locale = session('locale');
        } else {
            $locale = 'en';
        }

        // set laravel localization
        app()->setLocale($locale);

        // continue request
        return $next($request);
    }
}
