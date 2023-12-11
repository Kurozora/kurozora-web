<?php

namespace App\Actions\Web\Auth;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfHasLocalLibrary
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @param callable $next
     *
     * @return mixed
     */
    public function handle(Request $request, callable $next): mixed
    {
        if (method_exists($request, 'hasLocalLibrary')) {
            $data = $request->hasLocalLibrary();
        } else if (method_exists($request, 'validated')) {
            $data = $request->validated();
        } else {
            $data = [
                'hasLocalLibrary' => $request->boolean('hasLocalLibrary')
            ];
        }

        if ($data === true || ($data['hasLocalLibrary'] ?? false)) {
            return $this->hasLocalLibraryResponse($request);
        }

        return $next($request);
    }

    /**
     * Redirect user to the merge-library view.
     *
     * @param Request $request
     * @return Response
     */
    protected function hasLocalLibraryResponse(Request $request): Response
    {
        return redirect()->route('merge-library');
    }
}
