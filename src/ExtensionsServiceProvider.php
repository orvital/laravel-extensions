<?php

namespace Orvital\Extensions;

use Illuminate\Support\ServiceProvider;
use Orvital\Extensions\Database\Migrations\DatabaseMigrationRepository;
use Orvital\Extensions\Session\SessionManager;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use Orvital\Extensions\Database\Migrations\MigrationCreator;

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

        $this->app->extend('migration.creator', function ($repository, $app) {
            return new MigrationCreator($app['files'], $app->basePath('stubs'));
        });

        $this->app->singleton('session', function ($app) {
            return new SessionManager($app);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(192);

        $this->loadMigrationsFrom($this->getMigrationsPaths());
    }

    /**
     * Get the migrations paths including subdirectories.
     */
    protected function getMigrationsPaths(): array
    {
        $migrationsPath = database_path('migrations');
        $directories = File::directories($migrationsPath);
        return array_merge([$migrationsPath], $directories);
    }
}
