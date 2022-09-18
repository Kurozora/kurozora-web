<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HttpAccept
{
    /**
     * Accept header options.
     *
     * @var array|string[] $options
     */
    private array $options = [
        'html' => 'html/text',
        'json' => 'application/json',
    ];

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response|RedirectResponse) $next
     * @param string|null ...$options
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ?string ...$options): mixed
    {
        $acceptOptions = [];

        foreach ($options as $option) {
            $acceptOptions[] = $this->options[$option];
        }

        if (!empty($acceptOptions)) {
            $request->headers->set('Accept', implode(',', $acceptOptions));
            $response = $next($request);
            $response->withHeaders([
                'Accept' => $request->headers->get('Accept')
            ]);
            return $response;
        }

        return $next($request);
    }
}
