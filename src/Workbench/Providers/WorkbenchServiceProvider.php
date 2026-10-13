<?php

namespace Eclipse\Common\Workbench\Providers;

use Filament\FilamentServiceProvider;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Livewire\LivewireServiceProvider;
use function Orchestra\Sidekick\package_path;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->register(LivewireServiceProvider::class);
        $this->app->register(FilamentServiceProvider::class);
        $this->app->register(AdminPanelProvider::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../../database/workbench/migrations');

        Event::listen(CommandStarting::class, function ($event) {
            if (str_starts_with($event->command, 'boost:')) {
                // For boost commands, set the base path to the package path
                app()->setBasePath(package_path());
                app()->useAppPath(base_path('src'));

                config()->set('boost.code_environments.claude_code.guidelines_path', base_path('CLAUDE.md'));
            }
        });
    }
}
