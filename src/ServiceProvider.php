<?php

namespace Wedge\Validators\CommonPassword;

use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{
    protected $defer = false;

    /**
     * Publishes all the config file this package needs to function.
     */
    public function boot(Service $service)
    {
        $this->offerPublishing();
        $this->registerCommands();

        $this->app->bind(Service::class, function ($app) use ($service) {
            return $service;
        });

        Validator::extend('common_pwd', function ($attribute, $value, $parameters, $validator) use ($service) {
            return !$service->isCommonPassword($value);
        }, $this->app['config']['common-passwords.message']);
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/common-passwords.php',
            'common-passwords'
        );
    }

    protected function registerCommands()
    {
        $this->commands([
            Commands\SeedCommand::class,
        ]);
    }

    protected function offerPublishing()
    {
        if (! function_exists('config_path')) {
            // function not available and 'publish' not relevant in Lumen
            return;
        }

        $this->publishes([
            __DIR__.'/../config/common-password.php' => config_path('common-password.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../database/migrations/create_table_common_passwords.php.stub' => $this->getMigrationFileName('create_table_common_passwords.php'),
        ], 'migrations');
    }

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     *
     * @return string
     */
    protected function getMigrationFileName($migrationFileName): string
    {
        $timestamp = date('Y_m_d_His');

        $filesystem = $this->app->make(Filesystem::class);

        return Collection::make($this->app->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem, $migrationFileName) {
                return $filesystem->glob($path.'*_'.$migrationFileName);
            })
            ->push($this->app->databasePath()."/migrations/{$timestamp}_{$migrationFileName}")
            ->first();
    }
}
