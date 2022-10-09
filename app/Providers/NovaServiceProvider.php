<?php

namespace App\Providers;

use App\Http\Controllers\Web\Nova\SignInController;
use App\Models\User;
use App\Nova\Dashboards\Main;
use App\Nova\Tools\NovaPermissionTool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use KABBOUCHI\LogsTool\LogsTool;
use Laravel\Nova\Actions\ActionEvent;
use Laravel\Nova\Http\Controllers\LoginController;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        parent::boot();

        // Register custom sign-in controller
        $this->app->alias(SignInController::class, LoginController::class);

        // Set timezone to JST
        Nova::userTimezone(function (Request $request) {
            return 'Asia/Tokyo';
        });
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     *
     * @return void
     */
    protected function gate(): void
    {
        Gate::define('viewNova', function (User $user) {
            if (!$user->hasRole(['superAdmin', 'admin', 'mod', 'editor'])) {
                abort(redirect('https://discord.com/channels/449250093623934977/1021013118408724480/1021150221054521344'));
            }
            return true;
        });
    }

    /**
     * Configure the Nova authorization services.
     *
     * @return void
     */
    protected function authorization(): void
    {
        $this->gate();

        Nova::auth(function ($request) {
            return Gate::check('viewNova', [$request->user()]);
        });
    }

    /**
     * Get the dashboards that should be listed in the Nova sidebar.
     *
     * @return array
     */
    protected function dashboards(): array
    {
        return [
            new Main,
        ];
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array
     */
    public function tools(): array
    {
        return [
            new NovaPermissionTool,
            (new LogsTool)
                ->canSee(function ($request) {
                    return $request->user()->hasRole('superAdmin');
                })
                ->canDownload(function ($request) {
                    return  $request->user()->hasRole('superAdmin');
                })
                ->canDelete(function ($request) {
                    return $request->user()->hasRole('superAdmin');
                }),
        ];
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        // Disable action events
        ActionEvent::saving(function ($actionEvent) {
            return false;
        });
    }
}
