<?php

namespace Eclipse\Common;

use Eclipse\Common\Foundation\Providers\PackageServiceProvider;
use Spatie\LaravelPackageTools\Package as SpatiePackage;

class CommonServiceProvider extends PackageServiceProvider
{
    public static string $name = 'eclipse-common';

    public function configurePackage(SpatiePackage|Package $package): void
    {
        $package->name(static::$name)
            ->hasTranslations();
    }

    public function register(): self
    {
        parent::register();

        // Plugin class must be set as singleton
        $this->app->singleton(CommonPlugin::class);

        // Set translation loading in register method, so that the nav file is ready when the panel is being configured
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'eclipse-common');

        return $this;
    }
}
