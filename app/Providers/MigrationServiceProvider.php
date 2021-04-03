<?php

namespace App\Providers;

use App\Console\Commands\FreshCommand;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Console\Migrations\MigrateCommand;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Database\MigrationServiceProvider as BaseMigrationServiceProvider;

class MigrationServiceProvider extends BaseMigrationServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        parent::register();
        $this->registerMigrateCommand();
        $this->registerMigrateFreshCommand();
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerMigrateCommand()
    {
        $this->app->bindIf(ConnectionResolverInterface::class, 'db');
        $this->app->bindIf(MigrationRepositoryInterface::class, 'migration.repository');
        $this->app->singleton('command.migrate', function ($app) {
            return new MigrateCommand($app['migrator'], $app[Dispatcher::class]);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerMigrateFreshCommand()
    {
        $this->app->singleton('command.migrate.fresh', function () {
            return new FreshCommand;
        });
    }
}
