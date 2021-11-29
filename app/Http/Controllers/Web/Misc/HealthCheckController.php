<?php

namespace App\Http\Controllers\Web\Misc;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HealthCheckController extends Controller
{
    /**
     * Returns a simple hello world for AWS ELB Health Check.
     *
     * @param Request $request
     * @return Application|Response|ResponseFactory
     */
    public function index(Request $request): Application|Response|ResponseFactory
    {
        config()->set('session.driver', 'array');
        return response('Hello World!', 200)
            ->header('Content-Type', 'text/plain');
    }
}
