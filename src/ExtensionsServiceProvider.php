<?php

namespace Orvital\Extensions;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Orvital\Extensions\Auth\Passwords\PasswordBrokerManager;
use Orvital\Extensions\Console\Commands\RouteListModCommand;
use Orvital\Extensions\Database\Migrations\DatabaseMigrationRepository;
use Orvital\Extensions\Database\Migrations\MigrationCreator;
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
        $this->mergeConfigFrom(__DIR__.'/../config/extensions.php', 'extensions');

        // Deferred Providers
        $this->app->extend('migration.repository', function ($repository, $app) {
            return new DatabaseMigrationRepository($app['db'], $app['config']['database.migrations']);
        });

        $this->app->extend('migration.creator', function ($repository, $app) {
            return new MigrationCreator($app['files'], $app->basePath('stubs'));
        });

        $this->app->extend('auth.password', function ($repository, $app) {
            return new PasswordBrokerManager($app);
        });

        // Not Deferred Providers
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
        $this->publishes([
            __DIR__.'/../config/extensions.php' => $this->app->configPath('extensions.php'),
        ]);

        Schema::defaultStringLength(config('extensions.schema.default_string_length'));

        $this->loadMigrationsFrom($this->getMigrationsPaths());

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'extensions');

        if ($this->app->runningInConsole()) {
            $this->commands([
                RouteListModCommand::class,
            ]);
        }
    }

    /**
     * Get the migrations paths including subdirectories.
     */
    protected function getMigrationsPaths(): array
    {
        $migrationsPath = $this->app->databasePath('migrations');
        $directories = File::directories($migrationsPath);

        return array_merge([$migrationsPath], $directories);
    }
}
