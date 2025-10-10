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
            ->hasTranslations()
            ->hasViews()
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
            Css::make('media-gallery-styles', __DIR__.'/../resources/css/media-gallery.css'),
            Js::make('media-gallery-scripts', __DIR__.'/../resources/js/media-gallery.js'),
            Css::make('slider-column-styles', __DIR__.'/../resources/dist/slider-column.css'),
            Js::make('slider-column-scripts', __DIR__.'/../resources/dist/slider-column.js'),
        ], 'eclipse-common');

        FilamentView::registerRenderHook(
            'panels::body.end',
            fn (): string => view('eclipse-common::components.slider-column-lightbox')->render()
        );
    }
}
