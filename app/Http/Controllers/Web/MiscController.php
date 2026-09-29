<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\File;

class MiscController extends Controller
{
    /**
     * Show the team page.
     *
     * @return Application|Factory|View
     */
    public function team(): Application|Factory|View
    {
        $staff = User::whereIn('id', [
            2, 380, 461, 668, 1110, 2116
        ])
            ->with(['media'])
            ->get();
        $exStaff = User::whereIn('id', [
            1
        ])
            ->with(['media'])
            ->get();

        return view('misc.team', [
            'staff' => [
                $staff[0],
                $staff[3],
                $staff[2],
                $staff[1],
                $staff[4],
                $staff[5],
            ],
            'exStaff' => [
                $exStaff[0],
            ],
            'userCount' => number_shorten(User::count(), 1, true),
        ]);
    }

    /**
     * Show the projects page.
     *
     * @return Application|Factory|View
     */
    public function projects(): Application|Factory|View
    {
        return view('misc.projects', [
            'projects' => json_decode(File::get(resource_path('docs/projects.json')))->projects,
        ]);
    }

    /**
     * Show the contact page.
     *
     * @return Application|Factory|View
     */
    public function contact(): Application|Factory|View
    {
        return view('misc.contact');
    }

    /**
     * Show the press kit.
     *
     * @return Application|Factory|View
     */
    public function pressKit(): Application|Factory|View
    {
        return view('misc.press-kit');
    }

    /**
     * Show the API documentation.
     *
     * @return Application|Factory|View
     */
    public function apiIndex(): Application|Factory|View
    {
        return view('misc.api-index');
    }
}
