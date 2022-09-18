<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HttpContentSecurityPolicy
{
    /**
     * The CSP rules to set in the header.
     *
     * @var array $rules
     */
    private array $rules = [];

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response|RedirectResponse) $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        if ($request->routeIs('embed.songs') || $request->routeIs('embed.episodes')) {
             $this->rules[] = 'frame-ancestors *';
        }

        $cspRules = collect($this->rules)->join('; ');
        $response->withHeaders([
            'Content-Security-Policy' => $cspRules
        ]);

        return $response;
    }
}
