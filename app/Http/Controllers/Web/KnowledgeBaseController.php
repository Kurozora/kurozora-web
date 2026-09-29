<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class KnowledgeBaseController extends Controller
{
    /**
     * Show the developer token guide.
     *
     * @return Application|Factory|View
     */
    public function generatingDeveloperTokens(): Application|Factory|View
    {
        return view('knowledge-base.generating-developer-tokens');
    }

    /**
     * Show the community guidelines.
     *
     * @return Application|Factory|View
     */
    public function guidelines(): Application|Factory|View
    {
        return view('knowledge-base.guidelines');
    }

    /**
     * Show the in-app purchases article.
     *
     * @return Application|Factory|View
     */
    public function inAppPurchases(): Application|Factory|View
    {
        return view('knowledge-base.in-app-purchases');
    }

    /**
     * Show the personalization article.
     *
     * @return Application|Factory|View
     */
    public function personalization(): Application|Factory|View
    {
        return view('knowledge-base.personalization');
    }
}
