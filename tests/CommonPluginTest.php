<?php

use Eclipse\Common\CommonPlugin;
use Eclipse\Common\Admin\Filament\Clusters\Settings;
use Eclipse\Common\Exceptions\InvalidConfigurationException;
use Illuminate\Support\Facades\Config;

it('can get and set settings cluster', function () {
    $plugin = new CommonPlugin();

    expect($plugin->getSettingsCluster())->toBe(Settings::class);

    $plugin->settingsCluster('NewCluster');
    expect($plugin->getSettingsCluster())->toBe('NewCluster');

    $plugin->settingsCluster(null);
    expect($plugin->getSettingsCluster())->toBeNull();
});

it('can get available locales from array config', function () {
    // We need to use a new instance because of the static variable in getAvailableLocales
    $plugin = new CommonPlugin();

    Config::set('eclipse-common.available_locales', ['en', 'sl']);

    $locales = $plugin->getAvailableLocales();

    expect($locales)->toBeArray()
        ->and($locales)->toHaveCount(2)
        ->and($locales)->toContain('English (en)')
        ->and($locales)->toContain('Slovenian (sl)');
});

it('can get available locales from callable config', function () {
    $plugin = new CommonPlugin();

    Config::set('eclipse-common.available_locales', fn() => ['en', 'de']);

    $locales = $plugin->getAvailableLocales();

    expect($locales)->toBeArray()
        ->and($locales)->toHaveCount(2)
        ->and($locales)->toContain('English (en)')
        ->and($locales)->toContain('German (de)');
});

it('throws exception for invalid locales configuration', function () {
    $plugin = new CommonPlugin();

    Config::set('eclipse-common.available_locales', 'invalid');

    expect(fn() => $plugin->getAvailableLocales())
        ->toThrow(InvalidConfigurationException::class, 'Configuration "eclipse-common.available_locales" must be an array or a callable that returns an array.');
});

it('throws exception for empty locales configuration', function () {
    $plugin = new CommonPlugin();

    Config::set('eclipse-common.available_locales', []);

    expect(fn() => $plugin->getAvailableLocales())
        ->toThrow(InvalidConfigurationException::class, 'Configuration "eclipse-common.available_locales" must contain at least one locale.');
});
