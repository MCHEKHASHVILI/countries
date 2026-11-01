<?php

declare(strict_types=1);

namespace Mchekhashvili\Countries;

use Illuminate\Support\ServiceProvider;
use Mchekhashvili\Countries\Console\SyncCountries;

final class CountriesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/countries.php', 'countries');
    }

    public function boot(): void
    {
        if (config('countries.run_migrations')) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }

        if ($this->app->runningInConsole()) {
            $this->commands([SyncCountries::class]);
            $this->publishes([__DIR__ . '/../config/countries.php' => config_path('countries.php')], 'countries-config');
            $this->publishes([__DIR__ . '/../database/migrations' => database_path('migrations')], 'countries-migrations');
        }
    }
}
