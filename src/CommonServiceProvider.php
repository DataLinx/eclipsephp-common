<?php

namespace Eclipse\Common;

use Eclipse\Common\Foundation\Providers\PackageServiceProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentView;
use Spatie\LaravelPackageTools\Package as SpatiePackage;

class CommonServiceProvider extends PackageServiceProvider
{
    public static string $name = 'eclipse-common';

    public function configurePackage(SpatiePackage|Package $package): void
    {
        $package->name(static::$name)
            ->hasViews()
            ->hasTranslations()
            ->hasAssets();
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

    public function bootingPackage(): void
    {
        FilamentAsset::register([
            Css::make('slider-column', asset('vendor/eclipse-common/slider-column.css')),
            Js::make('slider-column', asset('vendor/eclipse-common/slider-column.js')),
        ], 'eclipse-common');

        FilamentView::registerRenderHook(
            'panels::body.end',
            fn (): string => view('eclipse-common::components.slider-column-lightbox')->render()
        );
    }
}
