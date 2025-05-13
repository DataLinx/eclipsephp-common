<?php

use Eclipse\Common\Package;

test('root path is correctly returned', function () {
    $package = new Package;
    $package->setBasePath('/test/base/path');

    // Test without directory parameter
    expect($package->rootPath())->toBe('/test/base/path/../');

    // Test with directory parameter
    expect($package->rootPath('test-dir'))->toBe('/test/base/path/../test-dir');
});

test('hasSettings throws exception when settings path does not exist', function () {
    $package = new Package;
    $package->setBasePath('/test/base/path');

    expect(fn () => $package->hasSettings())
        ->toThrow(RuntimeException::class, 'Settings path does not exist: /test/base/path/Settings');
});

test('hasSettings throws exception when migrations path does not exist', function () {
    $package = new Package;
    $package->setBasePath(__DIR__.'/../../workbench/app/TestPackage1/src');

    expect(fn () => $package->hasSettings())
        ->toThrow(RuntimeException::class, 'Settings migrations path does not exist: '.$package->rootPath('database/settings'));
});

test('hasSettings configures paths when both directories exist', function () {
    $package = new Package;
    $package->setBasePath(__DIR__.'/../../workbench/app/TestPackage2/src');

    $package->hasSettings();

    expect(config('settings.auto_discover_settings'))->toContain($package->basePath('Settings'))
        ->and(config('settings.migrations_paths'))->toContain($package->rootPath('database/settings'));
});
