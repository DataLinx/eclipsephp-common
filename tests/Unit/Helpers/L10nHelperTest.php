<?php

use Eclipse\Common\Exceptions\InvalidConfigurationException;
use Eclipse\Common\Helpers\L10nHelper as Helper;
use Illuminate\Support\Facades\Config;

test('it returns empty string for empty language code', function () {
    expect(Helper::getLanguageName(''))->toBe('');
});

test('it returns the language name for a given code', function () {
    // Assuming English is available
    $name = Helper::getLanguageName('en');

    expect($name)->toBe('English');
});

test('it returns the language name with code for a given code', function () {
    $name = Helper::getLanguageName('en', true);

    expect($name)->toBe('English (en)');
});

test('it handles language codes with underscores', function () {
    $name = Helper::getLanguageName('en_US');

    // Depending on ICU version and environment, this might return 'English' or 'English (United States)'
    // But for display language only it should be 'English'
    expect($name)->toBe('English');
});

test('it handles more specific language codes', function () {
    $name = Helper::getLanguageName('sl_SI');

    // Ensure it works for other languages too
    expect($name)->toBe('Slovenian');
});

test('it handles language codes with hyphens', function () {
    $name = Helper::getLanguageName('en-GB');
    expect($name)->toBe('English');
});

test('it includes the code in the output when requested', function () {
    $name = Helper::getLanguageName('en', true);
    expect($name)->toBe('English (en)');
});

test('it returns the code for unknown/invalid languages if intl is not helping', function () {
    $name = Helper::getLanguageName('xyz');
    expect($name)->toBe('xyz');
});

test('it trims the language code', function () {
    $name = Helper::getLanguageName('  en  ');

    expect($name)->toBe('English');
});

test('it can get available locales from array config', function () {
    Config::set('eclipse-common.available_locales', ['en', 'sl']);

    $locales = Helper::getAvailableLocales();

    expect($locales)->toBeArray()
        ->and($locales)->toHaveCount(2)
        ->and($locales)->toBe(['en' => 'en', 'sl' => 'sl']);
});

test('it can get available locales from callable config', function () {
    Config::set('eclipse-common.available_locales', fn () => ['en', 'de']);

    $locales = Helper::getAvailableLocales();

    expect($locales)->toBeArray()
        ->and($locales)->toHaveCount(2)
        ->and($locales)->toBe(['en' => 'en', 'de' => 'de']);
});

test('it throws exception for invalid locales configuration', function () {
    Config::set('eclipse-common.available_locales', 'invalid');

    expect(fn () => Helper::getAvailableLocales())
        ->toThrow(InvalidConfigurationException::class, 'Configuration "eclipse-common.available_locales" must be an array or a callable that returns an array.');
});

test('it throws exception for empty locales configuration', function () {
    Config::set('eclipse-common.available_locales', []);

    expect(fn () => Helper::getAvailableLocales())
        ->toThrow(InvalidConfigurationException::class, 'Configuration "eclipse-common.available_locales" must contain at least one locale.');
});

test('it can get options for locales', function () {
    Config::set('eclipse-common.available_locales', ['en', 'sl']);

    $options = Helper::getLocaleOptions();

    expect($options)->toBeArray()
        ->and($options)->toHaveCount(2)
        ->and($options)->toBe(['en' => 'English (en)', 'sl' => 'Slovenian (sl)']);
});
