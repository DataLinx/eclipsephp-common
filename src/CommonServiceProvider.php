<?php

namespace Eclipse\Common;

use Eclipse\Common\Foundation\Providers\PackageServiceProvider;
use Exception;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Columns\ImageColumn;
use Spatie\LaravelPackageTools\Package as SpatiePackage;

class CommonServiceProvider extends PackageServiceProvider
{
    public static string $name = 'eclipse-common';

    public function configurePackage(SpatiePackage|Package $package): void
    {
        $package->name(static::$name)
            ->hasViews()
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

    public function bootingPackage(): void
    {
        ImageColumn::macro(
            'preview',
            function (?callable $config = null) {
                return $this->extraImgAttributes(function ($record, $column) use ($config): array {
                    $imageUrls = $column->getState();
                    if (! is_array($imageUrls)) {
                        $imageUrls = $imageUrls ? [$imageUrls] : [];
                    }

                    $imageUrls = array_filter($imageUrls, function ($url): bool {
                        return ! str_starts_with($url, 'data:image/svg+xml;base64,');
                    });

                    if (empty($imageUrls)) {
                        return [];
                    }

                    $lightboxData = [];
                    foreach ($imageUrls as $index => $imageUrl) {
                        try {
                            $configData = $config ? $config($record, $column) : [];

                            if (is_array($configData) && isset($configData[0]) && is_array($configData[0])) {
                                $configData = $configData[0];
                            }

                            $lightboxData[] = [
                                'url' => $imageUrl,
                                'title' => $configData['title'] ?? '',
                                'link' => $configData['link'] ?? '',
                            ];
                        } catch (Exception $e) {
                            $lightboxData[] = [
                                'url' => $imageUrl,
                                'title' => '',
                                'link' => '',
                            ];
                        }
                    }

                    return [
                        'class' => 'cursor-pointer image-preview-trigger',
                        'onclick' => 'event.stopPropagation(); return false;',
                        'data-lightbox-config' => json_encode($lightboxData),
                    ];
                });
            }
        );

        FilamentView::registerRenderHook(
            'panels::body.end',
            fn (): string => view('eclipse-common::components.image-preview-modal')->render()
        );
    }
}
