<?php

use Eclipse\Common\Helpers\LocalizationHelper as Helper;

test('it returns empty string for empty language code', function () {
    expect(Helper::getLanguageNameFromCode(''))->toBe('');
});

test('it returns the language name for a given code', function () {
    // Assuming English is available
    $name = Helper::getLanguageNameFromCode('en');

    expect($name)->toBe('English');
});

test('it returns the language name with code for a given code', function () {
    $name = Helper::getLanguageNameFromCode('en', true);

    expect($name)->toBe('English (en)');
});

test('it handles language codes with underscores', function () {
    $name = Helper::getLanguageNameFromCode('en_US');

    // Depending on ICU version and environment, this might return 'English' or 'English (United States)'
    // But for display language only it should be 'English'
    expect($name)->toBe('English');
});

test('it handles more specific language codes', function () {
    $name = Helper::getLanguageNameFromCode('sl_SI');

    // Ensure it works for other languages too
    expect($name)->toBe('Slovenian');
});

test('it handles language codes with hyphens', function () {
    $name = Helper::getLanguageNameFromCode('en-GB');
    expect($name)->toBe('English');
});

test('it includes the code in the output when requested', function () {
    $name = Helper::getLanguageNameFromCode('en', true);
    expect($name)->toBe('English (en)');
});

test('it returns the code for unknown/invalid languages if intl is not helping', function () {
    $name = Helper::getLanguageNameFromCode('xyz');
    expect($name)->toBe('xyz');
});

test('it trims the language code', function () {
    $name = Helper::getLanguageNameFromCode('  en  ');

    expect($name)->toBe('English');
});
