<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CacheableGuestResponse
{
    /**
     * The request attribute marking a response as publicly cacheable.
     *
     * @var string
     */
    public const string ATTRIBUTE = 'cacheable';

    /**
     * The path prefixes that always carry session state.
     *
     * @var array
     */
    protected array $excluded = [
        'sign-in',
        'sign-up',
        'sign-out',
        'forgot-password',
        'reset-password',
        'verify-email',
        'two-factor-challenge',
        'siwa/*',
        'me',
        'me/*',
        'settings',
        'settings/*',
        'profile/*/edit',
        'nova',
        'nova/*',
        'nova-api/*',
        'livewire/*',
        'api',
        'api/*',
    ];

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isEligible($request)) {
            $request->attributes->set(static::ATTRIBUTE, true);
        }

        $response = $next($request);

        if ($request->attributes->get(static::ATTRIBUTE, false) && $this->isCacheable($request, $response)) {
            $response->headers->remove('Set-Cookie');
            $response->headers->set('Cache-Control', $this->cacheControl());
        }

        return $response;
    }

    /**
     * Determine whether the request may produce a publicly cacheable response.
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function isEligible(Request $request): bool
    {
        return config('cache.public_pages.enabled', false)
            && $request->isMethod('GET')
            && !$request->is($this->excluded)
            && $request->getHost() === parse_url(config('app.url'), PHP_URL_HOST)
            && !$request->hasHeader('X-Livewire');
    }

    /**
     * Determine whether the response may be stored by a shared cache.
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return bool
     */
    protected function isCacheable(Request $request, Response $response): bool
    {
        if ($response->getStatusCode() !== 200
            || !str_contains((string) $response->headers->get('Content-Type'), 'text/html')
            || !$request->hasSession()
            || auth()->check()) {
            return false;
        }

        return !$request->session()->has('errors')
            && empty($request->session()->get('_flash.new', []));
    }

    /**
     * The cache control directives for a publicly cacheable response.
     *
     * @return string
     */
    protected function cacheControl(): string
    {
        $ttl = (int) config('cache.public_pages.ttl', 600);

        return 'public, max-age=0, s-maxage=' . $ttl
            . ', stale-while-revalidate=' . $ttl * 6
            . ', stale-if-error=86400';
    }
}
