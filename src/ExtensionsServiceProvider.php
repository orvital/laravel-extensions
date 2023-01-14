<?php

namespace Orvital\Extensions;

use Illuminate\Support\ServiceProvider;
use Orvital\Extensions\Migration\DatabaseMigrationRepository;
use Orvital\Extensions\Session\SessionManager;

class ExtensionsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->extend('migration.repository', function ($repository, $app) {
            return new DatabaseMigrationRepository($app['db'], $app['config']['database.migrations']);
        });

        $this->app->singleton('session', function ($app) {
            return new SessionManager($app);
        });

}
