<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;

class PreventRequestsDuringMaintenance extends Middleware
{
    /**
     * The URIs that should be reachable while maintenance mode is enabled.
     *
     * @var array
     */
    protected $except = [
        //
    ];

    /**
     * Get the URIs that should be accessible even when maintenance mode is enabled.
     *
     * @return array
     */
    public function getExcludedPaths(): array
    {
        return array_merge(parent::getExcludedPaths(), [
            '*/forgot-password*',
            '*/email*',
            '*/reset-password*',
            '*/two-factor-challenge*',
            '*/user/two-factor-authentication*',
            '*/user/confirmed-two-factor-authentication*',
            '*/user/two-factor-authentication*',
            '*/user/two-factor-qr-code*',
            '*/user/two-factor-secret-key*',
            '*/user/two-factor-recovery-codes*',
            '*/v1*',
            route('.well-known.apple-app-site-association', [] , false),
            route('api.info', [] , false),
            route('misc.health-check', [] , false),
            route('liap.serverNotifications', [] , false),
            '*/liap*',
        ]);
    }
}
