<?php

namespace MakiDizajnerica\Snapshoter;

use Illuminate\Support\ServiceProvider;
use MakiDizajnerica\Snapshoter\SnapshotManager;

class SnapshoterServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('makidizajnerica-snapshoter', fn ($app) => $app->make(SnapshotManager::class));
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }
}
