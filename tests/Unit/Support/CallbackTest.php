<?php

use Eclipse\Common\Helpers\L10nHelper;
use Eclipse\Common\Support\Callback;
use Illuminate\Support\Facades\Config;
use Tests\Support\ConfigLocaleCallback;
use Tests\Support\InvokableLocaleCallback;

test('it can be created from class method', function () {
    $callback = new Callback(ConfigLocaleCallback::class, 'getLocales');

    expect($callback)->toBeInstanceOf(Callback::class)
        ->and($callback->toCallable())->toBe([ConfigLocaleCallback::class, 'getLocales'])
        ->and($callback())->toBe(['en', 'sl', 'de']);
});

test('it throws exception when creating from non-existent class or method', function () {
    expect(fn () => new Callback('NonExistentClass', 'getLocales'))
        ->toThrow(InvalidArgumentException::class, 'Specified class name or method name does not exist');

    expect(fn () => new Callback(ConfigLocaleCallback::class, 'nonExistentMethod'))
        ->toThrow(InvalidArgumentException::class, 'Specified class name or method name does not exist');
});

test('it can be created from invokable class', function () {
    $callback = new Callback(InvokableLocaleCallback::class);

    expect($callback)->toBeInstanceOf(Callback::class)
        ->and($callback->toCallable())->toBeInstanceOf(InvokableLocaleCallback::class)
        ->and($callback())->toBe(['en', 'sl', 'de']);
});

test('it throws exception when creating from non-invokable class without method', function () {
    expect(fn () => new Callback(ConfigLocaleCallback::class))
        ->toThrow(InvalidArgumentException::class, 'Specified class is not invokable');
});

test('it can be created from function', function () {
    $callback = new Callback('strtoupper');

    expect($callback)->toBeInstanceOf(Callback::class)
        ->and($callback->toCallable())->toBe('strtoupper')
        ->and($callback('hello'))->toBe('HELLO');
});

test('it throws exception when creating from non-existent function', function () {
    expect(fn () => new Callback('non_existent_function_12345'))
        ->toThrow(InvalidArgumentException::class, 'Specified function name is not callable');
});

test('it throws exception when invoking uninitialized callback', function () {
    $callback = Callback::__set_state([]);

    expect(fn () => $callback())
        ->toThrow(RuntimeException::class, 'Callback is not properly initialized');

    expect(fn () => $callback->toCallable())
        ->toThrow(RuntimeException::class, 'Callback is not properly initialized');
});

test('it can be serialized and unserialized', function () {
    $callback = new Callback(ConfigLocaleCallback::class, 'getLocales');

    $serialized = serialize($callback);
    $unserialized = unserialize($serialized);

    expect($unserialized)->toBeInstanceOf(Callback::class)
        ->and($unserialized())->toBe(['en', 'sl', 'de']);

    $functionCallback = new Callback('trim');
    $unserializedFunction = unserialize(serialize($functionCallback));

    expect($unserializedFunction)->toBeInstanceOf(Callback::class)
        ->and($unserializedFunction('  test  '))->toBe('test');

    $invokableCallback = new Callback(InvokableLocaleCallback::class);
    $unserializedInvokable = unserialize(serialize($invokableCallback));

    expect($unserializedInvokable)->toBeInstanceOf(Callback::class)
        ->and($unserializedInvokable())->toBe(['en', 'sl', 'de']);
});

test('it supports var_export via set_state for config caching', function () {
    $callback = new Callback(ConfigLocaleCallback::class, 'getLocales');

    $exported = var_export($callback, true);
    $restored = eval("return $exported;");

    expect($restored)->toBeInstanceOf(Callback::class)
        ->and($restored())->toBe(['en', 'sl', 'de']);

    $invokableCallback = new Callback(InvokableLocaleCallback::class);
    $exportedInvokable = var_export($invokableCallback, true);
    $restoredInvokable = eval("return $exportedInvokable;");

    expect($restoredInvokable)->toBeInstanceOf(Callback::class)
        ->and($restoredInvokable())->toBe(['en', 'sl', 'de']);
});

test('it can be used as a callback for available_locales config value', function () {
    $callback = new Callback(ConfigLocaleCallback::class, 'getLocales');

    Config::set('eclipse-common.available_locales', $callback);

    $locales = L10nHelper::getAvailableLocales();

    expect($locales)->toBeArray()
        ->and($locales)->toBe([
            'en' => 'en',
            'sl' => 'sl',
            'de' => 'de',
        ]);
});

test('it can be used as a callback for available_locales when config is cached with var_export', function () {
    $callback = new Callback(ConfigLocaleCallback::class, 'getLocales');

    // Simulate config caching (var_export -> eval)
    $exported = var_export($callback, true);
    $restored = eval("return $exported;");

    Config::set('eclipse-common.available_locales', $restored);

    $locales = L10nHelper::getAvailableLocales();
    $options = L10nHelper::getLocaleOptions();

    expect($locales)->toBe([
        'en' => 'en',
        'sl' => 'sl',
        'de' => 'de',
    ])->and($options)->toBe([
        'en' => 'English (en)',
        'sl' => 'Slovenian (sl)',
        'de' => 'German (de)',
    ]);
});
