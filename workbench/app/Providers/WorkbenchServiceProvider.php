<?php

namespace Workbench\App\Providers;

use Filament\FilamentServiceProvider;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Livewire\LivewireServiceProvider;

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
        Event::listen(CommandStarting::class, function ($event) {
            if (str_starts_with($event->command, 'boost:')) {
                // Make sure this actually points to your package root!
                app()->setBasePath(realpath(__DIR__.'/../../../'));
                app()->useAppPath(base_path('src'));

                config()->set('boost.code_environments.claude_code.guidelines_path', base_path('CLAUDE.md'));
            }
        });
    }
}
