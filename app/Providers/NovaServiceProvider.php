<?php

namespace App\Providers;

use App\Models\User;
use App\Nova\Dashboards\Main;
use App\Nova\Dashboards\UserInsights;
use App\Nova\Permission;
use App\Nova\Role;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Actions\ActionEvent;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;
use Stepanenko3\LogsTool\LogsTool;
use Stepanenko3\NovaCommandRunner\CommandRunnerTool;
use Vyuldashev\NovaPermission\NovaPermissionTool;

if (class_exists('Laravel\Nova\NovaApplicationServiceProvider')) {
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

            // Breadcrumbs
            Nova::withBreadcrumbs();

//            // Right-to-Left
//            Nova::enableRTL(function (Request $request) {
//                return $request->user()->wantsRTL();
//            });

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
                Main::make(),
                UserInsights::make()
                    ->showRefreshButton()
                    ->canSee(function ($request) {
                        return $request->user()->hasRole('superAdmin');
                    }),
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
                (new CommandRunnerTool)
                    ->canSee(function ($request) {
                        return $request->user()?->hasRole('superAdmin') ?? false;
                    }),
                (new NovaPermissionTool)
                    ->rolePolicy(RolePolicy::class)
                    ->permissionPolicy(PermissionPolicy::class)
                    ->roleResource(Role::class)
                    ->permissionResource(Permission::class),
                (new LogsTool)
                    ->canSee(function ($request) {
                        return $request->user()?->hasRole('superAdmin') ?? false;
                    })
                    ->canDownload(function ($request) {
                        return $request->user()?->hasRole('superAdmin') ?? false;
                    })
                    ->canDelete(function ($request) {
                        return $request->user()?->hasRole('superAdmin') ?? false;
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
}
